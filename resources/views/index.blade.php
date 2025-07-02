<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>IndexedDB CRUD with Laravel and jQuery</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">

            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Product Manager (IndexedDB CRUD)</h4>
                </div>

                <div class="card-body">
                    <!-- Add Product Form -->
                    <form id="addForm" class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Product Name</label>
                            <input type="text" id="name" class="form-control" placeholder="Enter product name" required>
                        </div>
                        <div class="col-md-4">
                            <label for="price" class="form-label">Price (₹)</label>
                            <input type="number" id="price" class="form-control" placeholder="Enter price" required>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-success w-100">Add</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Product List -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Product List</h5>
                </div>
                <div class="card-body p-0">
                    <ul id="productList" class="list-group list-group-flush"></ul>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Bootstrap JS & jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    let db;
    let request = indexedDB.open("ProductDB", 1);

    request.onerror = function(event) {
        console.log("Error opening DB", event);
    };

    request.onupgradeneeded = function(event) {
        db = event.target.result;
        let objectStore = db.createObjectStore("products", { keyPath: "id", autoIncrement: true });
        objectStore.createIndex("name", "name", { unique: false });
        objectStore.createIndex("price", "price", { unique: false });
        console.log("DB setup complete");
    };

    request.onsuccess = function(event) {
        db = event.target.result;
        console.log("DB opened");
        displayData();
    };

    $('#addForm').on('submit', function(e) {
        e.preventDefault();
        debugger;
        let name = $('#name').val();
        let price = parseFloat($('#price').val());
        let transaction = db.transaction(["products"], "readwrite");
        let store = transaction.objectStore("products");
        let product = { name: name, price: price };
        let addRequest = store.add(product);

        addRequest.onsuccess = function() {
            $('#name').val('');
            $('#price').val('');
            displayData();
        };
    });

    function displayData() {
        $('#productList').empty();
        let transaction = db.transaction(["products"], "readonly");
        let store = transaction.objectStore("products");
        let request = store.openCursor();

        request.onsuccess = function(event) {
            let cursor = event.target.result;
            if (cursor) {
                $('#productList').append(
                    `<li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${cursor.value.name}</strong>
                            <span class="text-muted">- ₹${cursor.value.price}</span>
                        </div>
                        <div>
                            <button class="btn btn-sm btn-primary me-2" onclick="editProduct(${cursor.value.id},'${cursor.value.name}',${cursor.value.price})">Edit</button>
                            <button class="btn btn-sm btn-danger" onclick="deleteProduct(${cursor.value.id})">Delete</button>
                        </div>
                    </li>`
                );
                cursor.continue();
            } else if (!$('#productList').children().length) {
                $('#productList').append(
                    `<li class="list-group-item text-center text-muted">No products found.</li>`
                );
            }
        };
    }

    function deleteProduct(id) {
        let transaction = db.transaction(["products"], "readwrite");
        let store = transaction.objectStore("products");
        store.delete(id).onsuccess = function() {
            displayData();
        };
    }

    function editProduct(id, oldName, oldPrice) {
        let name = prompt("Edit name:", oldName);
        let price = parseFloat(prompt("Edit price:", oldPrice));
        if(name && !isNaN(price)){
            let transaction = db.transaction(["products"], "readwrite");
            let store = transaction.objectStore("products");
            let getRequest = store.get(id);

            getRequest.onsuccess = function() {
                let data = getRequest.result;
                data.name = name;
                data.price = price;
                store.put(data).onsuccess = function() {
                    displayData();
                };
            };
        }
    }
</script>

</body>
</html>
