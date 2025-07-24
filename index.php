<?php 
require_once('constants.php');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
  <title>Stripe India Test Payment</title>
  <script src="https://js.stripe.com/v3/"></script>
  <style>
    #payment-form {
      max-width: 400px;
      margin: 50px auto;
    }
  </style>
</head>
<body>

  <form id="amount-form">
    <label>Enter Amount (₹):</label><br>
    <input type="number" id="amount-input" placeholder="e.g. 500" required>
    <button type="submit">Continue to Payment</button>
  </form>

  <form id="payment-form" style="display:none;">
    <div id="payment-element"></div>
    <button id="submit" style="margin-top:10px;">Pay</button>
    <div id="error-message"></div>
  </form>

  <script>
    var ST_PK = '<?php echo STRIPE_API_PUBLISH_KEY;?>';
    const stripe = Stripe(ST_PK);
    let elements;

    document.getElementById('amount-form').addEventListener('submit', async function(e) {
      e.preventDefault();
      const amount = document.getElementById('amount-input').value;

      if (!amount || amount < 1) {
        alert("Enter a valid amount (greater than 0)");
        return;
      }

      // Convert rupees to paise
      const amountInPaise = parseInt(amount) * 100;

      const res = await fetch("create_intent.php", {
        method: "POST",
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ amount: amountInPaise })
      });

      const data = await res.json();

      if (data.error) {
        alert(data.error);
        return;
      }

      elements = stripe.elements({ clientSecret: data.clientSecret });
      const paymentElement = elements.create("payment");
      paymentElement.mount("#payment-element");

      document.getElementById('payment-form').style.display = 'block';
      document.getElementById('amount-form').style.display = 'none';

      document.getElementById("payment-form").addEventListener("submit", async (e) => {
        e.preventDefault();
        const { error } = await stripe.confirmPayment({
          elements,
          confirmParams: {
            return_url: "http://localhost/corephp/api_stripe/"
          }
        });

        if (error) {
          document.getElementById("error-message").textContent = error.message;
        }
      });
    });
  </script>
</body>
</html>
