(function () {
    let controller = { abort: () => {} };
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

    storyblokInstance.on(['input'], async ({ story }) => {
        controller.abort();

        controller = new AbortController();
        const { signal } = controller;

        try {
            const request = await fetch('/storyblok/index/ajax', {
                signal,
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    story,
                    _storyblok: story.id
                })
            });
            const response = await request.json();
            const blockId = Object.keys(response)[0];
            const comments = parseStoryblokComments();

            comments[blockId].comment.remove();
            comments[blockId].element.outerHTML = response[blockId];

            storyblokInstance.enterEditmode();
        } catch (err) {
            return;
        }
    });
})();
