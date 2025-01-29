document.addEventListener("DOMContentLoaded", function () {
    let page = 1;
    let loading = false;

    const seedInput = document.getElementById("seed");
    const languageSelect = document.getElementById("language");
    const likesInput = document.getElementById("likes");
    const reviewsInput = document.getElementById("reviews");
    const addButton = document.getElementById("add-books");
    const exportButton = document.getElementById("export-csv");
    const tableBody = document.getElementById("books-table");

    function loadBooks() {
        if (loading) return;
        loading = true;

        console.log(`📚 Loading books... Page: ${page}`);

        fetch(`/api/books?page=${page}&seed=${seedInput.value}&language=${languageSelect.value}&likes=${likesInput.value}&reviews=${reviewsInput.value}`)
            .then(response => response.json())
            .then(data => {
                if (!Array.isArray(data) || data.length === 0) {
                    console.warn("⚠ API вернул пустой массив или неверные данные!", data);
                    loading = false;
                    return;
                }

                console.log(`✅ Loaded ${data.length} books`);

                data.forEach(book => {
                    let row = document.createElement("tr");
                    row.innerHTML = `
                        <td>${book.index}</td>
                        <td>${book.isbn}</td>
                        <td>${book.title}</td>
                        <td>${book.author}</td>
                        <td>${book.publisher}</td>
                        <td>
                            <button class="btn btn-link toggle-details" data-id="${book.index}">
                                <i class="fas fa-chevron-down"></i>
                            </button>
                        </td>
                    `;

                    let detailsRow = document.createElement("tr");
                    detailsRow.classList.add("details-row", "collapse");
                    detailsRow.id = `details-${book.index}`;
                    detailsRow.innerHTML = `
                        <td colspan="6">
                            <div class="p-3 border rounded">
                                <div class="row">
                                    <div class="col-md-3">
                                        <img class="img-fluid rounded shadow-sm" src="${book.cover}" alt="Book Cover">
                                    </div>
                                    <div class="col-md-9">
                                        <h4>${book.title}</h4>
                                        <p><strong>Author:</strong> ${book.author}</p>
                                        <p><strong>Publisher:</strong> ${book.publisher}</p>
                                        <p><strong>Likes:</strong> ${book.likes}</p>
                                        <h5>Reviews:</h5>
                                        <ul class="list-group">
                                            ${book.reviews.length > 0
                        ? book.reviews.map(r => `<li class="list-group-item"><strong>${r.author}:</strong> ${r.text}</li>`).join("")
                        : "<li class='list-group-item text-muted'>No reviews</li>"
                    }
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </td>
                    `;

                    row.querySelector(".toggle-details").addEventListener("click", function () {
                        let icon = this.querySelector("i");
                        let details = document.getElementById(`details-${book.index}`);

                        if (details.classList.contains("show")) {
                            details.classList.remove("show");
                            icon.classList.replace("fa-chevron-up", "fa-chevron-down");
                        } else {
                            details.classList.add("show");
                            icon.classList.replace("fa-chevron-down", "fa-chevron-up");
                        }
                    });

                    tableBody.appendChild(row);
                    tableBody.appendChild(detailsRow);
                });

                page++;
                loading = false;
            })
            .catch(error => {
                console.error("❌ Ошибка при загрузке книг:", error);
                loading = false;
            });
    }

    addButton.addEventListener("click", () => {
        console.log("🔄 Кнопка: Добавить 20 книг");
        for (let i = 0; i < 2; i++) loadBooks(); // 2 вызова = 20 книг
    });

    exportButton.addEventListener("click", () => {
        console.log("📂 Экспорт в CSV");
        window.location.href = `/api/export?pages=${page}&seed=${seedInput.value}&language=${languageSelect.value}&likes=${likesInput.value}&reviews=${reviewsInput.value}`;
    });

    window.addEventListener("scroll", () => {
        if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 200) {
            console.log("📜 Прокрутка внизу, загружаем ещё 10 книг...");
            loadBooks();
        }
    });

    loadBooks();
});
