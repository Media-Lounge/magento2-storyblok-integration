define(['jquery', 'storyblok'], ($, storyblok) => {
    'use strict';

    return () => {
        let request = { abort: () => {} };
        const { StoryblokBridge } = window;
        const storyblokInstance = new StoryblokBridge({
            preventClicks: true
        });

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

        storyblokInstance.on(['published', 'change'], () => window.location.reload());

        storyblokInstance.on(['input'], ({ story }) => {
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

                storyblokInstance.enterEditmode();
            });
        });
    };
});
