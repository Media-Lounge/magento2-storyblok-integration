define(['jquery', 'storyblok'], ($, storyblok) => {
    'use strict';

    return function (config) {
        let request = { abort: () => {} };

        function enterEditMode() {
            if (storyblok.inEditor) {
                storyblok.enterEditmode();
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

            request = $.get('/storyblok/ajax/index', {
                story,
                _storyblok: story.id
            });

            request.then((response) => {
                let curNode;
                const comments = {};
                const iterator = document.createNodeIterator(
                    document.body,
                    NodeFilter.SHOW_COMMENT,
                    () => NodeFilter.FILTER_ACCEPT,
                    false
                );
                const blockId = Object.keys(response)[0];

                while ((curNode = iterator.nextNode())) {
                    if (curNode.textContent.includes('#storyblok#')) {
                        const nodeData = curNode.textContent.replace('#storyblok#', '');
                        const jsonData = JSON.parse(nodeData);

                        comments[jsonData.uid] = {
                            comment: curNode,
                            element: curNode.nextElementSibling
                        };
                    }
                }

                $(comments[blockId].comment).remove();
                $(comments[blockId].element).replaceWith(response[blockId]);

                enterEditMode();
            });
        });

        storyblok.pingEditor(() => enterEditMode());
    };
});
