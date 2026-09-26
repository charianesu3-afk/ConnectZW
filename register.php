<?php

include 'includes/config.php';


$pageTitle = "Register | ConnectZW";


$message = "";



if(isset($_POST['register'])){


$name = mysqli_real_escape_string($conn,$_POST['name']);

$email = mysqli_real_escape_string($conn,$_POST['email']);

$password = $_POST['password'];

$role = $_POST['role'];





// Check existing email


$check = mysqli_query(
$conn,
"SELECT id FROM users WHERE email='$email'"
);



if(mysqli_num_rows($check)>0){


$message = "Email already registered.";


}else{



// Hash password

$hashed_password = password_hash(
$password,
PASSWORD_DEFAULT
);





$sql = "INSERT INTO users
(name,email,password,role)

VALUES

('$name','$email','$hashed_password','$role')";





if(mysqli_query($conn,$sql)){


$message = "Registration successful. You can now login.";


}else{


$message = "Registration failed. Please try again.";


}



}



}



include 'includes/header.php';

include 'includes/navbar.php';


?>




<section class="search-page">


<div class="container">



<div class="card">


<h1>

Create ConnectZW Account

</h1>




<?php if($message!=""){ ?>


<p>

<?php echo $message; ?>

</p>


<?php } ?>







<form method="POST">



<label>

Full Name

</label>


<input 
type="text"
name="name"
required
>

<br><br>




<label>

Email Address

</label>


<input

type="email"

name="email"

required

>


<br><br>






<label>

Password

</label>


<input

type="password"

name="password"

required

>


<br><br>







<label>

Account Type

</label>



<select name="role">


<option value="customer">

Customer

</option>



<option value="business">

Business Owner

</option>


</select>



<br><br>



<button type="submit" name="register">

Create Account

</button>




</form>



<p>

Already have an account?

<a href="login.php">

Login here

</a>

</p>



</div>


</div>


</section>





<?php include 'includes/footer.php'; ?>