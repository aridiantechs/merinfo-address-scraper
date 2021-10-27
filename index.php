<!DOCTYPE html>
<html>
<head>
    <title>Busniness Numbers Verification</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</head>
<body>
    <div class="container" style="margin-top: 5rem;">

        <div class="mb-5 mt-5">
            <h2>Business numbers verification</h2>
        </div>

        <div id="alert" class="mb-5">
            
        </div>
        <div class="row">
            
            <div class="col-sm-6">
                <form action="#" method="" enctype="multipart/form-data">
                    <h5>Upload file</h5>
                    <div class="d-flex">
                        <input type="file" name="fileToUpload" id="fileToUpload" placeholder="New File Name" accept=".txt" class="form-control">
                        <button type="" class="btn btn-success" id="upload">Upload</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row mt-5 pt-5">
            <div class="col-sm-12">
                <table class="table table-stripped">
                    <tr>
                        <th>No. </th>
                        <th>File Path</th>
                        <th>Action</th>
                    </tr>

                    <?php
                        $files  = glob("uploads/*.txt");
                        
                        foreach($files as $key => $file) {
                            ?>
                                <tr>
                                    <td><?php echo $key + 1 ?></td>
                                    <td><?php echo $file ?></td>
                                    <td><a target="_blank" href="<?php echo $file ?>">View File</a></td>
                                </tr>
                        <?php } ?>
                    
                </table>
            </div>
            
        </div>


    </div>
</body>
<script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>
<script type="text/javascript">
    $('#upload').on('click', function(e) {
        e.preventDefault();
        var file_data = $('#fileToUpload').prop('files')[0];   
        var form_data = new FormData();                  
        form_data.append('fileToUpload', file_data);
        
        $('#alert').html('<div class="alert alert-info" role="alert">We are processing your file. Please bear with us.</div>');
        jQuery.ajax({
            url: 'scrap.php', 
            dataType: 'text',  
            cache: false,
            contentType: false,
            processData: false,
            data: form_data,                         
            type: 'post',
            success: function(response){
                $('#alert').html(response); 
            }
         });
    });
</script>
</html>