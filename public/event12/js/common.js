function bootStarpSuccessAlert(content){
        if(content) {
            var htmlContent = "<div class='alert alert-success msg' id='msg' >"+content+"<button type='button' class='close' data-dismiss='alert' aria-label='Close'> <span aria-hidden='true'>&times;</span> </button> </div>"
            return htmlContent ;
        }
    }
    
    function bootStarpDangerAlert(content){
        if(content) {
            var htmlContent = "<div class='alert alert-danger errmsg' id='errmsg' >"+content+"<button type='button' class='close' data-dismiss='alert' aria-label='Close'> <span aria-hidden='true'>&times;</span> </button> </div>"
            return htmlContent ;
        }
    }