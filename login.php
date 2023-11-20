<?php
// Initialize the session
session_start();
 
// Check if the user is already logged in, if yes then redirect him to welcome page accordingly
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: dashboard.php");
    exit;
}
 

// include database and object files
include_once 'config/database.php';

// get database connection
$database = new Database();
$db = $database->getConnection();

  // include users
  include_once 'objects/users.php';
  // pass connection to objects
  $users = new UserRole($db);
  // Read user's role 

// Include config file
require_once "config.php";
 
// Define variables and initialize with empty values
$username = $password = "";
$username_err = $password_err = $login_err = "";
 
// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    // Check if username is empty
    if(empty(trim($_POST["username"]))){
        $username_err = "Please enter username.";
    } else{
        $username = trim($_POST["username"]);
    }
     
    // Check if username is not a valid email address
    function valid_email($username) {
        return (!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $username)) ? FALSE : TRUE;
        }
        
        if(!valid_email($username)){
            $username_err = "Please enter a valid e-mail address.";
        }else{
            $username = trim($_POST["username"]);
        }
    
    // Check if password is empty
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter your password.";
    } else{
        $password = trim($_POST["password"]);
    }
          
    // Validate credentials
    if(empty($username_err) && empty($password_err)){
        // Prepare a select statement
        $sql = "SELECT id, username, password FROM users WHERE username = ?";
        
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            
            // Set parameters
            $param_username = $username;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Store result
                mysqli_stmt_store_result($stmt);
                
                // Check if username exists, if yes then verify password
                if(mysqli_stmt_num_rows($stmt) == 1){                    
                    // Bind result variables
                    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password);
                    if(mysqli_stmt_fetch($stmt)){
                        if(password_verify($password, $hashed_password)){
                            // Password is correct, so start a new session
                            session_start();
                            
                            // Store data in session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;  
                            
                            // Insert user login in table user_logins
                            $con = mysqli_connect("localhost","root","","energypolis");
                            if (mysqli_connect_errno()) {
                            echo "Failed to connect to MySQL: " . mysqli_connect_error();
                            exit();
                            }
                            // Store user login in users_login table
                            $sql = "INSERT INTO `user_logins`( `user_id`) 
                            VALUES ('$id')";
                               if ($result = mysqli_query($con, $sql)) {
                               }
                               mysqli_close($con);

                            
                            // Redirect user to welcome page
                            header("location: dashboard.php");
                        } else{
                            // Password is not valid, display a generic error message
                            $login_err = "Invalid username or password.";
                        }
                    }
                } else{
                    // Username doesn't exist, display a generic error message
                    $login_err = "Invalid username or password.";
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
    
    // Close connection
    mysqli_close($link);
}
?>
 


 <!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="EnergyPolis Monitoring Platform - Sion - CH" />
        <meta name="author" content="Ing. Ermanno Lo Cascio, PhD" />
        <title>EnergyPolis - Login</title>
        <link href="css/styles.css" rel="stylesheet" />
        <link rel="icon" type="image/x-icon" href="assets/icons/logo.png" />
        <script data-search-pseudo-elements defer src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/js/all.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.28.0/feather.min.js" crossorigin="anonymous"></script>
    </head>
   

    <body>
        <div id="layoutAuthentication" >
         <div id="layoutAuthentication_content">
          <main>
           <div class="container">
            <div class="row">
             <div class="col-8 col-sm-4">
              <p class="title">EnergyPolis</p>
              <p class="subtitle">Data fusion platform</p>
             </div>
            </div>
            <div class="container">
             <div class="row justify-content-center">
              <div class="col-lg-5">
               <!-- Basic login form-->
               <div class="card shadow-lg border-0 rounded-lg mt-5">
                <div id='login_enabled'>
                 <div class="card-header justify-content-center"><h3 class="font-weight-light my-4">Login</h3></div>
                 <div class="card-body">
                    <?php 
                    if(!empty($login_err)){
                        echo '<div class="alert alert-danger">' . $login_err . '</div>';
                    }        
                    ?>
                     <!-- Login form-->
                  <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                   <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                    <span class="invalid-feedback"><?php echo $username_err; ?></span>
                   </div> 
                   <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                    <span class="invalid-feedback"><?php echo $password_err; ?></span>
                   </div>
                   <div class="form-group">
                    <div class="form-group d-flex align-items-center justify-content-between mt-4 mb-0">
                     <a class="small" href="password_recovery.php">Forgot Password?</a>
                     <input type="submit" class="btn btn-primary" value="Login">
                    </div>
                   </div>
                  </form>
                 </div>
                </div>
                <div class="text-center">
                 <hr>
                 <div class="small"><a href="register.php">Need an account? Sign up!</a></div>
                 <p></p>
                </div>
               </div>
              </div>
             </div>
            </div>
           </main>
           </div>
           <div id="layoutAuthentication_footer">
            <!-- Include footer -->

<?php require_once 'includes/footer_home.php' ?>
<?php require_once 'includes/cookies.php' ?>

