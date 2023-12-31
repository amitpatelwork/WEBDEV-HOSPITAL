<?php
// include 'header.php'
?>

<?php
$mysqli = new mysqli('localhost', 'root', '', 'PBL3-HOSPITAL');
// if(isset($_GET['date'])){
//     $date = $_GET['date'];
//     $stmt = $mysqli->prepare("select * from bookings where date = ?");
//     $stmt->bind_param('s', $date);
//     $bookings = array();
//     if($stmt->execute()){
//         $result = $stmt->get_result();
//     }
// }



if(isset($_GET['date'])){
    $date = $_GET['date'];
    $stmt = $mysqli->prepare("select * FROM appoinment where date = ?");
    $stmt->bind_param('s', $date);
    $bookings = array();
    if($stmt->execute()){
        $result = $stmt->get_result();
        if($result->num_rows>0){
            while($row = $result->fetch_assoc()){
                $bookings[] = $row['timeslot'];
            }
            $stmt->close();
        }
    }
}
if(isset($_POST['submit'])){
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $gender = $_POST['gender'];
    $agegroup = $_POST['agegroup'];
    $disease = $_POST['diseases'];
    $timeslot = $_POST['timeslot'];
    $place = "BHOPAL";
    $caseby = "BY ADMIN";
    $stat = "Not Confirm";
    $stmt = $mysqli->prepare("select * from appoinment where date = ? AND timeslot=?");
    $stmt->bind_param('ss', $date, $timeslot);
    if($stmt->execute()){
        $result = $stmt->get_result();
        if($result->num_rows>0){
            $msg = "<div class='alert alert-danger'>Already Booked</div>";
        }else{
            $stmt = $mysqli->prepare("INSERT INTO appoinment (fname, phone,gender,agegroup,category,place,date,caseby,stat,gmail,timeslot) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param('sssssssssss', $name, $phone,$gender,$agegroup,$disease,$place,$date,$caseby,$stat,$email,$timeslot);
            $stmt->execute();
            $msg = "<div class='alert alert-success'>Booking Successfull</div>";
            $bookings[] = $timeslot;
            $stmt->close();
            $mysqli->close();
        }
    }
}


//  initialize few variables.
$duration = 15;
$cleanup = 0;
$start = "10:00";
$end = "12:00";

// FUNCTION FOR TIME SLOT 

function timeslots($duration, $cleanup, $start, $end){
    $start = new DateTime($start);
    $end = new DateTime($end);
    $interval = new DateInterval("PT".$duration."M");
    $cleanupInterval = new DateInterval("PT".$cleanup."M");
    $slots = array();
    
    for($intStart = $start; $intStart<$end; $intStart->add($interval)->add($cleanupInterval)){
        $endPeriod = clone $intStart;
        $endPeriod->add($interval);
        if($endPeriod>$end){
            break;
        }
        
        $slots[] = $intStart->format("H:iA")." - ". $endPeriod->format("H:iA");
        
    }
    
    return $slots;
}



?>
<!doctype html>
<html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title></title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link rel="stylesheet" href="/css/main.css">
  </head>

  <body>

    <div style="margin-top: 170px; margin-bottom: 200px;" class="container">
        <h1 class="text-center">Book for Date: <?php echo date('m/d/Y', strtotime($date)); ?></h1><hr>
        <!-- <div class="row">
           <div class="col-md-6 col-md-offset-3">
//one line is deleted by me
               <form action="" method="post">
                   <div class="form-group">
                       <label for="">Name</label>
                       <input required type="text" class="form-control" name="name">
                   </div>
                   <div class="form-group">
                       <label for="">Email</label>
                       <input required type="email" class="form-control" name="email">
                   </div>
                   <div class="form-group">
                       <button name="submit" type="submit" class="btn btn-primary">Submit</button>
                   </div>
               </form>
           </div>
            
        </div>
    </div> -->


    <div class="row">
<div class="col-md-12">
   <?php echo(isset($msg))?$msg:""; ?>
</div>
<?php $timeslots = timeslots($duration, $cleanup, $start, $end); 
    foreach($timeslots as $ts){
?>
<div class="col-md-2">
    <div class="form-group">
       <?php if(in_array($ts, $bookings)){ ?>
       <button class="btn btn-danger"><?php echo $ts; ?></button>
       <?php }else{ ?>
       <button class="btn btn-success book" data-timeslot="<?php echo $ts; ?>"><?php echo $ts; ?></button>
       <?php }  ?>
    </div>
</div>
<?php } ?>
</div>
        </div>


        <div id="myModal" class="modal fade" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Booking for: <span id="slot"></span></h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <form action="" method="post">
                               <div class="form-group">
                                    <label for="">Time Slot</label>
                                    <input readonly type="text" class="form-control" id="timeslot" name="timeslot">
                                </div>
                                <div class="form-group">
                                    <label for="">Name</label>
                                    <input required type="text" class="form-control" name="name">
                                </div>
                                <div class="form-group">
                                    <label for="">Email</label>
                                    <input required type="email" class="form-control" name="email">
                                </div>
                                <div class="form-group">
                                    <label for="">phone</label>
                                    <input required type="number" class="form-control" name="phone">
                                </div>

                                <!-- <div class="form-group">
                                <label>Select hour</label>
                                <input name="select_hour" type ="number" value="1" min='1' max='10' class="form-control"  style="width:100px; text-align:center;">
                                </div> -->

                                <div class="form-group">
                                    <label for="">Gender</label>
                                    <!-- <input required type="number" class="form-control" name="phone"> -->
                                    
<select name="gender" class="form-control" required>

<option value selected >Select Value</option>
<option value="male">male</option>
<option value="female">female</option>


</select>

                                </div>

                                <div class="form-group">
                                    <label for="">Age Group</label>
                                    <!-- <input required type="number" class="form-control" name="phone"> -->
                                    
<select name="agegroup" class="form-control" required>

<option value selected >Select Value</option>
    <option value="14-17">14-17</option>
    <option value="18-21">18-21</option>
	<option value="22-25">22-25</option>
    <option value="26-29">26-29</option>
    <option value="30-33">30-33</option>
    <option value="34-37">34-37</option>
    <option value="38-41">38-41</option>


</select>

                                </div>
         
                                <div class="form-group">
                                    <label for="">Diseases</label>
                                    <!-- <input required type="number" class="form-control" name="phone"> -->
                                    
<select name="diseases" class="form-control" required>

<option value selected >Select Value</option>
    <option value="cold cough">cold cough</option>
    <option value="maleria">maleria</option>
	<option value="typhoid">typhoid</option>
    <option value="fever">fever</option>
    <option value="acidity">acidity</option>


</select>

                                </div>

                                <div class="form-group pull-right">
                                    <button name="submit" type="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
            </div>

        </div>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
    <script>
$(".book").click(function(){
    var timeslot = $(this).attr('data-timeslot');
    $("#slot").html(timeslot);
    $("#timeslot").val(timeslot);
    $("#myModal").modal("show");
});
</script> 
<?php
// include 'footer.php'
?>