(function () {
    let controller = { abort: () => {} };

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

    storyblok.on(['published', 'change'], () => window.location.reload());

    storyblok.on(['input'], async ({ story }) => {
        controller.abort();

        controller = new AbortController();
        const { signal } = controller;
        const storyContentWithComments = storyblok.addComments(story.content, story.id);

        story.content = storyContentWithComments;

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

            document.body.dispatchEvent(new Event('contentUpdated'));

            enterEditMode();
        } catch (err) {
            return;
        }
    });

    storyblok.pingEditor(() => enterEditMode());
})();
