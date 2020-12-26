define(['jquery', 'storyblok'], ($, storyblok) => {
    'use strict';

    return function (config) {
        let request = { abort: () => {} };

        function enterEditMode() {
            if (storyblok.inEditor) {
                storyblok.enterEditmode();
            }
        }

        function isStoryblokComment(node) {
            if (node.textContent.includes('#storyblok#')) {
                return NodeFilter.FILTER_ACCEPT;
            }
        }

        storyblok.init({
            accessToken: config.apiKey
        });

        storyblok.on(['published', 'change'], () => window.location.reload());

        storyblok.on(['input'], ({ story }) => {
            const storyContentWithComments = storyblok.addComments(story.content, story.id);

            story.content = storyContentWithComments;

            request.abort();

            request = $.post({
                url: '/storyblok/index/ajax',
                data: {
                    story,
                    _storyblok: story.id
                },
                global: false
            });

            request.then((response) => {
                let curNode;
                const comments = {};
                const htmlComments = document.createNodeIterator(
                    document.body,
                    NodeFilter.SHOW_COMMENT,
                    isStoryblokComment
                );
                const blockId = Object.keys(response)[0];

                while ((curNode = htmlComments.nextNode())) {
                    const nodeData = curNode.textContent.replace('#storyblok#', '');
                    const jsonData = JSON.parse(nodeData);

                    comments[jsonData.uid] = {
                        comment: curNode,
                        element: curNode.nextElementSibling
                    };
                }

                $(comments[blockId].comment).remove();
                $(comments[blockId].element).replaceWith(response[blockId]);

                $('body').trigger('contentUpdated');

                enterEditMode();
            });
        });

        storyblok.pingEditor(() => enterEditMode());
    };
});
