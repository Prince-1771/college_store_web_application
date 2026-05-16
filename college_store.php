<?php
session_start();

/* ================= PRODUCTS ================= */
$products = [
 "p1"=>["name"=>"Wireless Headphones","price"=>1500,"img"=>"https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=500&q=80"],
 "p2"=>["name"=>"Smartwatch","price"=>2500,"img"=>"https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=500&q=80"],
 "p3"=>["name"=>"Running Shoes","price"=>1200,"img"=>"https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=500&q=80"],
 "p4"=>["name"=>"College Backpack","price"=>850,"img"=>"https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=500&q=80"],
 "p5"=>["name"=>"Power Bank","price"=>999,"img"=>"https://images.unsplash.com/photo-1609091839311-d5365f9ff1c5?w=500&q=80"],
 "p6"=>["name"=>"Bluetooth Speaker","price"=>1800,"img"=>"https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=500&q=80"]
];

if(!isset($_SESSION['cart'])) $_SESSION['cart']=[];

/* ================= ADD TO CART ================= */
if(isset($_POST['add'])){
    $id=$_POST['id'];
    if(isset($_SESSION['cart'][$id]))
        $_SESSION['cart'][$id]['qty']++;
    else
        $_SESSION['cart'][$id]=[
            "name"=>$products[$id]['name'],
            "price"=>$products[$id]['price'],
            "qty"=>1
        ];
}

/* ================= CLEAR CART ================= */
if(isset($_POST['clear'])) $_SESSION['cart']=[];

$orderDone=false;
$orderHtml="";

/* ================= PLACE ORDER ================= */
if(isset($_POST['order']) && !empty($_SESSION['cart'])){

    /* ===== STRING FUNCTIONS ===== */
    $name = htmlspecialchars(trim($_POST['name']));
    $address = htmlspecialchars(trim($_POST['address']));
    $payment = $_POST['payment'];
    $upi = trim($_POST['upi_id'] ?? '');

    if(strlen($address)>60)
        $address = substr($address,0,60)."...";

    $subTotal=0;
    $rows="";

    foreach($_SESSION['cart'] as $it){
        $line=$it['price']*$it['qty'];
        $subTotal+=$line;

        $rows.="<tr>
                  <td>{$it['name']}</td>
                  <td>{$it['qty']}</td>
                  <td>₹$line</td>
                </tr>";
    }

    /* ===== MATH FUNCTIONS ===== */
    $gst=round($subTotal*0.18);
    $total=ceil($subTotal+$gst);
    $orderId=rand(10000,99999);

    /* ===== DATE FUNCTIONS ===== */
    date_default_timezone_set('Asia/Kolkata');
    $date=date("d-M-Y h:i A");
    $delivery=date("l, d F Y",strtotime("+4 days"));

    /* ===== PAYMENT TEXT ===== */
    if($payment=="UPI")
        $paymentText="UPI (ID: $upi)";
    elseif($payment=="QR")
        $paymentText="QR Code Payment";
    else
        $paymentText="Cash on Delivery";

    $orderHtml="
    <div class='success'>
      <h2>✅ Order #$orderId Confirmed</h2>
      <p>Placed on: $date</p>
      <p><b>Name:</b> $name</p>
      <p><b>Address:</b> $address</p>
      <p><b>Payment:</b> $paymentText</p>

      <p class='delivery'>🚚 Delivery by <b>$delivery</b></p>

      <table>
        <tr><th>Item</th><th>Qty</th><th>Total</th></tr>
        $rows
      </table>

      <p>Subtotal: ₹$subTotal</p>
      <p>GST: ₹$gst</p>
      <h3>Total Paid: ₹$total</h3>
    </div>";

    $_SESSION['cart']=[];
    $orderDone=true;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>College Store</title>

<style>
body{font-family:Arial;background:#f1f3f6;margin:0}
.header{background:#2874f0;color:#fff;text-align:center;padding:18px;font-size:26px;font-weight:bold}
.container{max-width:1100px;margin:auto;padding:20px;display:flex;gap:20px}
.left{flex:2}.right{flex:1}
.box{background:#fff;padding:20px;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,.08)}

.grid{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}
.card{border:1px solid #ddd;padding:12px;text-align:center;border-radius:8px}
.card img{width:100%;height:150px;object-fit:cover;border-radius:6px}
.price{color:#388e3c;font-size:20px;font-weight:bold;margin:8px 0}

button{background:#fb641b;color:#fff;border:none;padding:8px 14px;border-radius:6px;cursor:pointer;font-weight:bold}

.cart-item{display:flex;justify-content:space-between;border-bottom:1px solid #eee;padding:8px 0}

input,textarea{width:100%;padding:10px;margin:6px 0;border:1px solid #ccc;border-radius:6px}

.success{text-align:center}
.success table{width:100%;border-collapse:collapse;margin-top:15px}
.success th,.success td{border:1px solid #ddd;padding:8px}
.delivery{background:#e3f2fd;padding:10px;border-radius:6px;margin:10px 0}

.payment-box{background:#f9f9f9;padding:10px;border-radius:6px;margin-top:8px}
.qr{display:block;margin:10px auto;width:140px}
</style>
</head>

<body>

<div class="header">🛒 College Store</div>

<div class="container">

<?php if(!$orderDone){ ?>

<!-- LEFT PRODUCTS -->
<div class="box left">
<h3>Products</h3>

<div class="grid">
<?php foreach($products as $id=>$p){ ?>
<div class="card">
<img src="<?php echo $p['img']; ?>">
<h4><?php echo $p['name']; ?></h4>
<div class="price">₹<?php echo $p['price']; ?></div>

<form method="POST">
<input type="hidden" name="id" value="<?php echo $id; ?>">
<button name="add">Add to Cart</button>
</form>
</div>
<?php } ?>
</div>
</div>

<!-- RIGHT CART -->
<div class="box right">

<h3>Your Cart</h3>

<?php
$total=0;

if(empty($_SESSION['cart'])){
    echo "Cart is empty";
}else{

foreach($_SESSION['cart'] as $it){
$line=$it['price']*$it['qty'];
$total+=$line;
?>
<div class="cart-item">
<span><?php echo $it['name']." x ".$it['qty']; ?></span>
<b>₹<?php echo $line; ?></b>
</div>
<?php } 

$gst=round($total*0.18);
$final=ceil($total+$gst);
?>

<h3>Total: ₹<?php echo $final; ?></h3>

<form method="POST">
<button name="clear">Clear Cart</button>
</form>

<h3>Delivery Details</h3>

<form method="POST">
<input name="name" required placeholder="Full Name">
<textarea name="address" rows="3" required placeholder="Address"></textarea>

<h3>Payment Method</h3>

<div class="payment-box">
<label><input type="radio" name="payment" value="COD" checked> Cash on Delivery</label><br>

<label><input type="radio" name="payment" value="UPI"> UPI ID</label>
<input name="upi_id" placeholder="Enter UPI ID"><br>

<label><input type="radio" name="payment" value="QR"> QR Code</label>
<img class="qr" src="https://upload.wikimedia.org/wikipedia/commons/d/d0/QR_code_for_mobile_English_Wikipedia.svg">
</div>

<button name="order">Place Order</button>
</form>

<?php } ?>

</div>

<?php } else { ?>

<div class="box" style="width:100%">
<?php echo $orderHtml; ?>
</div>

<?php } ?>

</div>

</body>
</html>