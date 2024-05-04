function addToCart(productName) {
        // Implement your logic to add the product to the cart
        // For now, let's assume it's a simple increment of the cart count

        // Display the fly to cart animation
        let flyToCart = document.getElementById('flyToCart');
        flyToCart.innerText = `${productName} added to Cart!`;
        flyToCart.style.transform = 'translateY(0)';
        flyToCart.style.opacity = '1';

        setTimeout(() => {
            // Hide the fly to cart animation after 2 seconds
            flyToCart.style.transform = 'translateY(-100%)';
            flyToCart.style.opacity = '0';
        }, 2000);

        // You can also add logic to store the selected products in the cart
    }

    function navigateToCart() {
            // Redirect to the cart page
            window.location.href = "cart.php";
        }