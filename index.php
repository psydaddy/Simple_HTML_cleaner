<meta charset="utf-8">

<form enctype="multipart/form-data" method="POST" action="<?php $_SERVER['PHP_SELF']?>">
<textarea name="html" rows="20" cols="80"><?php echo @$_POST['html'] ?></textarea>
<br><br>
<input type="submit" value="Process" name="process">
</form>


<?php
include('include.php');

$html=@$_POST['html'];
$cleanHTML = cleanHtml($html);

//echo htmlentities($cleanHTML);
echo ($cleanHTML);

?>
