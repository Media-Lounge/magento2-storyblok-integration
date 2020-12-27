define(['jquery', 'storyblok'], ($, storyblok) => {
    'use strict';

    return ({ apiKey }) => {
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

        function parseStoryblokComments() {
            let curNode;
            const comments = {};
            const htmlComments = document.createNodeIterator(
                document.body,
                NodeFilter.SHOW_COMMENT,
                isStoryblokComment
            );

            while ((curNode = htmlComments.nextNode())) {
                const nodeData = curNode.textContent.replace('#storyblok#', '');
                const jsonData = JSON.parse(nodeData);

                comments[jsonData.uid] = {
                    comment: curNode,
                    element: curNode.nextElementSibling
                };
            }

            return comments;
        }

        storyblok.init({
            accessToken: apiKey
        });

        storyblok.on(['published', 'change'], () => window.location.reload());

        storyblok.on(['input'], ({ story }) => {
            const storyContentWithComments = storyblok.addComments(story.content, story.id);

            story.content = storyContentWithComments;

            request.abort();

            request = $.post({
                url: '/storyblok/index/ajax',
                contentType: 'application/json',
                data: JSON.stringify({
                    story,
                    _storyblok: story.id
                }),
                global: false
            });

            request.then((response) => {
                const blockId = Object.keys(response)[0];
                const comments = parseStoryblokComments();

                $(comments[blockId].comment).remove();
                $(comments[blockId].element).replaceWith(response[blockId]);

                $('body').trigger('contentUpdated');

                enterEditMode();
            });
        });

        storyblok.pingEditor(() => enterEditMode());
    };
});
