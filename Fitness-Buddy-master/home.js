document.addEventListener("DOMContentLoaded", function () {
    fetch("get_posts.php")
        .then(response => response.json())
        .then(posts => {
            const container = document.getElementById("posts-container");
            container.innerHTML = "";

            posts.forEach(post => {
                const postElement = document.createElement("div");
                postElement.classList.add("card", "mb-3");

postElement.innerHTML = `
    <div class="card-body">
        <h5 class="card-title">${escapeHtml(post.content)}</h5>
        <p class="card-text">
            <small class="text-muted">
                Posted by ${escapeHtml(post.username)} • ${new Date(post.created_at).toLocaleString()}
            </small>
        </p>
        <a href="post.php?id=${post.id}" class="btn btn-primary">View Post</a>
    </div>
`;

                container.appendChild(postElement);
            });
        })
        .catch(error => console.error("Error fetching posts:", error));
});

function escapeHtml(str) {
    const div = document.createElement("div");
    div.textContent = str;
    return div.innerHTML;
}