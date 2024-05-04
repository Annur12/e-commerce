var dropdowns = document.getElementsByClassName("dropdown-btn");
        var i;

        for (i = 0; i < dropdowns.length; i++) {
            dropdowns[i].addEventListener("click", function() {
                this.classList.toggle("active");
                var dropdownContent = this.nextElementSibling;
                if (dropdownContent.style.display === "block") {
                    dropdownContent.style.display = "none";
                } else {
                    dropdownContent.style.display = "block";
                }
            });
        }

var addProductBtn = document.getElementById('add-product-btn');
        var addProductForm = document.getElementById('add-product-form');
        var closeFormBtn = document.getElementById('close-form-btn');

        addProductBtn.addEventListener('click', function() {
            addProductForm.style.display = 'block';
        });

        closeFormBtn.addEventListener('click', function() {
            addProductForm.style.display = 'none';
        });

        // Search Button
        var searchBtn = document.getElementById('search-btn');

        searchBtn.addEventListener('click', function() {
            // Handle search functionality here
        });
