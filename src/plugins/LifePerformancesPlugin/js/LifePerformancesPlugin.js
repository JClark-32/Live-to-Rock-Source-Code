jQuery(document).ready(function($){
    $('#likeBtn').click(function(){
        var likeBtn = document.getElementById('likeBtn')
    
        if (likeBtn.classList.contains('btn-secondary')){
            likeBtn.classList.remove('btn-secondary')
            likeBtn.classList.add('btn-primary')
            $('#likeCount').text((parseInt($('#likeCount').text())+1))
        }
        else{
            likeBtn.classList.remove('btn-primary')
            likeBtn.classList.add('btn-secondary')
            $('#likeCount').text((parseInt($('#likeCount').text())-1))
        }
    });
});