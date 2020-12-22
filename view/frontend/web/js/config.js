define(['jquery', 'storyblok'], ($, storyblok) => {
    'use strict';

    return function (config) {
        let request = { abort: () => {} };

        storyblok.init({
            accessToken: config.apiKey
        });

        storyblok.on(['published', 'change'], () => window.location.reload());

        storyblok.on(['input'], ({ story }) => {
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

                        comments[jsonData.uid] = curNode.nextElementSibling;
                    }
                }

                $(comments[blockId]).replaceWith(response[blockId]);
            });
        });

        storyblok.pingEditor(() => {
            if (storyblok.inEditor) {
                storyblok.enterEditmode();
            }
        });
    };
});
