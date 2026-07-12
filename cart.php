<?php
include('./zel_admin/conn.php');
include('./lock.php');
require_once('./mail/class.phpmailer.php');
require_once('./mail/class.smtp.php');
$error="";
$show="display:none;";
$alert="alert alert-success";
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
 if(isset($_SESSION['login_euser']) && isset($_SESSION['login_password']))
{
}
else
{
  header("Location:login.php");
  exit;
}
if (isset($_GET['pid'])) {
	//$pid=$_GET['pid'];	
}
else{
	//header('Location:./home.php');
}
if(!isset($_SESSION)){
    session_start();
}
require_once("dbcontroller.php");
$db_handle = new DBController();
if(!empty($_GET["action"])) {
switch($_GET["action"]) {
	case "add":
		if(!empty($_GET["quantity"])) {
			$productByCode = $db_handle->runQuery("SELECT p.pid as code, p.pname, r.rate_id, r.price, p.imgpath FROM products p, rate r WHERE p.pid=r.pid AND p.pid='" . $_GET["pid"] . "'");
			$itemArray = array($productByCode[0]["code"]=>array('name'=>$productByCode[0]["pname"], 'code'=>$productByCode[0]["code"], 'quantity'=>$_GET["quantity"], 'price'=>$productByCode[0]["price"], 'image'=>$productByCode[0]["imgpath"]));
			
			if(!empty($_SESSION["cart_item"])) {
				
				if(in_array($productByCode[0]["code"], array_keys($_SESSION["cart_item"]))) {
					foreach($_SESSION["cart_item"] as $k => $v) {
							if($productByCode[0]["code"] == $k) {
								if(empty($_SESSION["cart_item"][$k]["quantity"])) {
									$_SESSION["cart_item"][$k]["quantity"] = 0;
								}
								$_SESSION["cart_item"][$k]["quantity"] += $_GET["quantity"];
							}
					}
				} else {
					$_SESSION["cart_item"] = array_merge($_SESSION["cart_item"],$itemArray);
				}
			} else {
				$_SESSION["cart_item"] = $itemArray;
			}
		}
	break;
	case "remove":
	
		if(!empty($_SESSION["cart_item"])) {
			foreach($_SESSION["cart_item"] as $k => $v) {
					if($_GET["code"] == $k){
						//echo $k;
						unset($_SESSION["cart_item"][$k]);
					}						
					if(empty($_SESSION["cart_item"])){
						unset($_SESSION["cart_item"]);
					}
			}
		}
	break;
	case "empty":
		unset($_SESSION["cart_item"]);
	break;	
}
}


if (isset($_POST['submitorder'])){
	if ($_SERVER["REQUEST_METHOD"] == "POST") {		
			$msg=order_add();
			$error=$msg;
			$show="display:show;";
			$alert="alert alert-success";
			unset($_SESSION["cart_item"]);
			//Email Code
			$subject="Hi Your Order is Successfully Placed at Our Website, we will contact you soon. Thank You";
			$body="Hi <b>".$login_name."</b>, <br/> Your Order is Successfully Placed at our website Thank You.<br/> <br/><br/><br/><b>Thank and Regards<br/>
			Unique Foods.<br/>";
			$mail = new PHPMailer;
			$mail->isSMTP();
			$mail->SMTPDebug = 1;
			$mail->Host = 'ssl://smtp.gmail.com';
			$mail->Port = 465;
			$mail->SMTPAuth = true;
			$mail->Username = 'uniquefood1315@gmail.com';
			$mail->Password = 'unique@1315';
			$mail->setFrom('uniquefood1315@gmail.com', 'Unique Food');
			$mail->addReplyTo('uniquefood1315@gmail.com', 'Unique Food');			
			$mail->Subject = $subject;
			$mail->msgHTML($body);
			$mail->Body = $body;
			$mail->addAddress($email, $login_name);
			if (!$mail->send()) {
				//echo 'Mailer Error: ' . $mail->ErrorInfo;
			}
			header("Location:./order_success.php?error=$error&show=$show&alert=$alert");
	}
}
function order_add(){
	include('./zel_admin/conn.php');
	include('./lock.php');
	$order_date = date("Y-m-d");
	$status=1;
	$currdate=date("Y-m-d");	
	$sql = "INSERT INTO orders (euid, order_date, order_status) VALUES ($user_id, '$order_date', $status)";
	if ($conn->query($sql) === TRUE) {
		$order_id = $conn->insert_id;
		$number = count($_POST["productName"]); 
		for($i=0; $i<$number; $i++)  
		{  
		   if(trim($_POST["productName"][$i] != ''))  
		   { 	   
			$productName=$_POST["productName"][$i];
			$quantity=$_POST["quantity"][$i];
			$rateValue=$_POST["rateValue"][$i];			
			$fmSql = "INSERT INTO order_item (order_id, pid, rate_id, qty, order_item_status) 
			VALUES ($order_id, $productName, $rateValue, '$quantity', 1)";
			$conn->query($fmSql);			
		   }  
		} 	
		$msg="Order Is Placed successfully!";	
	}
	else{
		$msg="Something Is Wrong! Please Try Again!";
	}
	return $msg;
}
if (isset($_GET['error'])) {
	$error=$_GET['error'];
	$show=$_GET['show'];
	$alert=$_GET['alert'];
}
function test_input($data) {
   $data = trim($data);
   $data = stripslashes($data);
   $data = htmlspecialchars($data);
   return $data;
}
?>
<!DOCTYPE html>
<html lang="en">
  
<!-- Mirrored from designing-world.com/suha-v2.1.0/cart.html by HTTrack Website Copier/3.x [XR&CO'2014], Thu, 17 Sep 2020 08:34:36 GMT -->
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover, shrink-to-fit=no">
    <meta name="description" content="Suha - Multipurpose Ecommerce Mobile HTML Template">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="theme-color" content="#100DD1">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <!-- The above tags *must* come first in the head, any other head content must come *after* these tags-->
    <!-- Title-->
    <title>Cart Confirm Order</title>
    <!-- Favicon-->
    <link rel="icon" href="img/icons/icon-72x72.png">
    <!-- Apple Touch Icon-->
    <link rel="apple-touch-icon" href="img/icons/icon-96x96.png">
    <link rel="apple-touch-icon" sizes="152x152" href="img/icons/icon-152x152.png">
    <link rel="apple-touch-icon" sizes="167x167" href="img/icons/icon-167x167.png">
    <link rel="apple-touch-icon" sizes="180x180" href="img/icons/icon-180x180.png">
    <!-- Stylesheet-->
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="style1.css">
    <!-- Web App Manifest-->
    <link rel="manifest" href="manifest.json">
	<script type="text/javascript">
	  function cal(){
		  var counter=0;
		  var f1 = document.getElementById("price").value;
		  var f2 = document.getElementById("qty").value;
		  var r= parseFloat(f1) * parseFloat(f2);
		  if(parseFloat(f2)<=0 || isNaN(f2)){
			document.getElementById("qty").value=1;  
		  }
		  if(!isNaN(r))
		  {
			document.getElementById("total").innerHTML=r.toFixed(2);
		  }
	   }
  </script>
  </head>
  <body>
    <!-- Preloader-->
    <div class="preloader" id="preloader">
      <div class="spinner-grow text-secondary" role="status">
        <div class="sr-only">Loading...</div>
      </div>
    </div>
    <!-- Header Area-->
    <div class="header-area" id="headerArea">
      <div class="container h-100 d-flex align-items-center justify-content-between">
        <!-- Back Button-->
        <div class="back-button"><a href="./home.php"><i class="lni lni-arrow-left"></i></a></div>
        <!-- Page Title-->
        <div class="page-heading">
          <h6 class="mb-0">My Cart</h6>
        </div>
        <!-- Navbar Toggler-->
        <div class="suha-navbar-toggler d-flex justify-content-between flex-wrap" id="suhaNavbarToggler"><span></span><span></span><span></span></div>
      </div>
    </div>
    <!-- Sidenav Black Overlay-->
   <?php
	include('sidenav.php');
	?>
    <div class="page-content-wrapper">
      <div class="container">
        <!-- Cart Wrapper-->
        <div class="cart-wrapper-area py-3">
		<div class="<?php echo $alert; ?>" role="alert" style="<?php echo $show; ?>"><?php echo $error; ?></div>
		<form method="post" action="cart.php">
          <div class="cart-table card mb-3">
            <div class="table-responsive card-body">
<div id="shopping-cart">
<div class="txt-heading">Shopping Cart</div>
<a id="btnEmpty" href="cart.php?action=empty">Empty Cart</a>
<?php
if(isset($_SESSION["cart_item"])){
    $total_quantity = 0;
    $total_price = 0;
?>	
<table class="tbl-cart" cellpadding="10" cellspacing="1">
<tbody>
<tr>
<th style="text-align:left;">Name</th>
<th style="text-align:right;" width="5%">Quantity</th>
<th style="text-align:right;" width="10%">Unit Price</th>
<th style="text-align:right;" width="10%">Price</th>
<th style="text-align:center;" width="5%">Remove</th>
</tr>	
<?php		
    foreach ($_SESSION["cart_item"] as $item){
        $item_price = $item["quantity"]*$item["price"];
		?>
				<tr>
				<td><img src="./zel_seller/<?php echo $item["image"]; ?>" class="cart-item-image1" /><?php echo $item["name"]; ?></td>
				<input type="hidden" name="productName[]" value="<?php echo $item["code"]; ?>"/>
				<input type="hidden" name="quantity[]" value="<?php echo $item["quantity"]; ?>"/>
				<input type="hidden" name="rateValue[]" value="<?php echo $item["quantity"]; ?>"/>
				<td style="text-align:right;"><?php echo $item["quantity"]; ?></td>
				<td  style="text-align:right;"><?php echo "Rs. ".$item["price"]; ?></td>
				<td  style="text-align:right;"><?php echo "Rs. ". number_format($item_price,2); ?></td>
				<td style="text-align:center;"><a href="cart.php?action=remove&code=<?php echo $item["code"]; ?>" class="btnRemoveAction"><img src="./img/icon-delete.png" alt="Remove Item" /></a></td>
				</tr>
				<?php
				$total_quantity += $item["quantity"];
				$total_price += ($item["price"]*$item["quantity"]);
		}
		?>

<tr>
<td colspan="1" align="right">Total:</td>
<td align="right"><?php echo $total_quantity; ?></td>
<td align="right" colspan="2"><strong><?php echo "Rs. ".number_format($total_price, 2); ?></strong></td>
<td></td>
</tr>
</tbody>
</table>		
  <?php
} else {
?>
<div class="no-records">Your Cart is Empty</div>
<?php 
}
?>
</div>
            </div>
          </div>
          <!-- Coupon Area-->
          <div class="card coupon-card mb-3" style="display:show;">
            <div class="card-body">
              <div class="apply-coupon">
                <h6 class="mb-0">Make Payment</h6>
                <p class="mb-2">Scan given QR Code for make a payment.</p>
                <div class="coupon-form">
                  <img src="./img/phonepay.jpg" class="img-responsive"/>
                  <img src="./img/upi-payments.png" class="img-responsive"/>
                </div>
              </div>
            </div>
          </div>
          <!-- Cart Amount Area-->
          <div class="card cart-amount-area">
            <div class="card-body d-flex align-items-center justify-content-between">
              <h5 class="total-price mb-0">Rs.<span class="counter" id="total"></span></h5>
			  <input type="submit" class="btn btn-warning" name="submitorder" value="Place Order"/>
			  <input type="button" class="btn btn-warning" href="cart.php?action=empty" name="empty" value="Empty Cart"/>
            </div>
          </div>
		  </form>
		  <div class="card coupon-card mb-3" style="display:none;">
            <div class="card-body">
              <div class="apply-coupon">
                <h6 class="mb-0">Have a coupon?</h6>
                <p class="mb-2">Enter your coupon code here &amp; get awesome discounts!</p>
                <div class="coupon-form">
                  <form action="#">
                    <input class="form-control" type="text" placeholder="SUHA30">
                    <button class="btn btn-primary" type="submit">Apply</button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Internet Connection Status-->
    <div class="internet-connection-status" id="internetStatus"></div>
    <!-- Footer Nav-->
    <?php 
include('footer.php');
?>
    <!-- All JavaScript Files-->
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/waypoints.min.js"></script>
    <script src="js/jquery.easing.min.js"></script>
    <script src="js/owl.carousel.min.js"></script>
    <script src="js/jquery.counterup.min.js"></script>
    <script src="js/jquery.countdown.min.js"></script>
    <script src="js/default/jquery.passwordstrength.js"></script>
    <script src="js/wow.min.js"></script>
    <script src="js/jarallax.min.js"></script>
    <script src="js/jarallax-video.min.js"></script>
    <script src="js/default/dark-mode-switch.js"></script>
    <script src="js/default/no-internet.js"></script>
    <script src="js/default/active.js"></script>
    <script src="js/pwa.js"></script>
  </body>

<!-- Mirrored from designing-world.com/suha-v2.1.0/cart.html by HTTrack Website Copier/3.x [XR&CO'2014], Thu, 17 Sep 2020 08:34:36 GMT -->
</html>