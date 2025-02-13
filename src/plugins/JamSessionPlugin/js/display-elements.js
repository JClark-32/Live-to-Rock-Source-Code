    //Reverses the entries of the arrays
    let blogIds = [];
    let blogTexts = [];
    let blogAuthors = [];
    let datesPosted = [];
    let blogLikes = [];

    function reverseArrays(){
        blogIds.reverse();
        blogTexts.reverse();
        blogTitles.reverse();
        blogAuthors.reverse();
        datesPosted.reverse();
        blogLikes.reverse();
    }
    
    function createBlogElements(){
        const blogContainer = document.getElementById("ltr-blogs-here");

        blogTexts.forEach((blogText, index) => {
            const postDiv = document.createElement("div");
            postDiv.classList.add("blog-post"+blogIds[index]);
            
            const hr = document.createElement("hr");
    
            const title = document.createElement("h2");
            title.textContent = blogTitles[index];
            
            const authorLabel = document.createElement("label");
            authorLabel.textContent = blogAuthors[index];
            
            const datePara = document.createElement("p");
            datePara.style.color = "gray";
            datePara.innerHTML = `<small>${datesPosted[index]}</small>`;
            
            const textPara = document.createElement("pre");
            textPara.style = "white-space: pre-wrap; word-break: keep-all;"
            textPara.textContent = blogText;
    
            const likeButton = document.createElement("button");
            likeButton.type = "button";
            likeButton.textContent = "Like";
            likeButton.name = "blog-likeBtn";
            likeButton.onclick = likeClick;
    
            const likeCount = document.createElement("span");
            likeCount.name = "likeCount";
            likeCount.textContent = blogLikes[index];
    
            const commentButton = document.createElement("button");
            commentButton.type = "button";
            commentButton.textContent = "Comments";
            commentButton.name = "blog-commentBtn";
            commentButton.onclick = commentClick;
    
            postDiv.appendChild(hr);
            postDiv.appendChild(title);
            postDiv.appendChild(authorLabel);
            postDiv.appendChild(datePara);
            postDiv.appendChild(textPara);
    
    
            
            if(!currentUser == '0'){
                postDiv.appendChild(likeButton);
                postDiv.appendChild(likeCount);
                postDiv.appendChild(commentButton);
            }
    
            blogContainer.appendChild(postDiv);    
    
        function likeClick(){
            jQuery(document).ready(function($){
                var postId = blogIds[index];
                $.ajax({
                    url:ajaxurl,
                    data:{
                        'action':'like_ajax_request',
                        'postID' :postId
                    },
                    success:function(data){
                        if(data == "liked"){
                            likeCount.textContent=parseInt(likeCount.textContent)+1;
                        }
                        else if(data == "unliked"){
                            likeCount.textContent=parseInt(likeCount.textContent)-1;
                        }
                    },
                    error:function(errorThrown){
                        window.alert("errorThrown");
                    }
                })
            })
            var blogPostId = blogIds[index];
            console.log(blogPostId);
        }

        var boxExists;

        function commentClick(){
            var commentsDiv = document.createElement("div");
            commentsDiv.id = "blog-comments"+blogIds[index];

            var input = document.createElement("input");
            input.type="text";
            input.id = "blog-comment-input";
            input.name="blog-commentInput";


            jQuery(document).ready(function($){
                var commentTexts = Array;
                var userCommented = Array;
                var commentsDatePosted = Array;
                var commentIds = Array;
                var postId = blogIds[index];
                $.ajax({
                    url:ajaxurl,
                    data:{
                        'action':'comments_clicked_ajax_request',
                        'postID' : postId
                    },
                    success:function(data){
                        var response = JSON.parse(data);
                        //alert(response.comment_texts);
                        commentTexts = (response.comment_texts);
                        userCommented = (response.comment_user_names);
                        commentsDatePosted = (response.comment_dates_posted);
                        commentIds = (response.comment_ids);


                        commentIds.forEach((commentId, index2) => {
                            
                            const commentDiv = document.createElement("div");
                            commentDiv.classList.add("comment"+blogIds[index]+commentIds[index2]);

                            const commentUserNameLabel = document.createElement("label");
                            commentUserNameLabel.textContent = userCommented[index2];

                            const commentDatePara = document.createElement("p");
                            commentDatePara.style.color = "gray";
                            commentDatePara.innerHTML = `<small>${commentsDatePosted[index2]}</small>`;

                            const commentText = document.createElement("p");
                            commentText.textContent = commentTexts[index2]; 

                            const commentHr =document.createElement("hr");
                            commentHr.style = "width:80%";
                            commentHr.color = "lightGray";


                            commentDiv.appendChild(commentUserNameLabel);
                            commentDiv.appendChild(commentDatePara);
                            commentDiv.appendChild(commentText);
                            commentDiv.appendChild(commentHr);

                            commentsDiv.appendChild(commentDiv);
                            
                        })
                    },
                    error:function(errorThrown){
                        window.alert("errorThrown");
                    }
                })
            })
        
            
            input.addEventListener("keydown", function(event) {
                if (event.key === "Enter") {
                    submitComment(input.value);
                    input.value = "";
                }
            });

            commentsDiv.append(input);

            var currentCommentsDiv = document.getElementById("blog-comments"+blogIds[index]);

            if (boxExists == true) {
                boxExists = false;
                currentCommentsDiv.remove();

            }
            else{
                boxExists = true;
                postDiv.appendChild(commentsDiv);
            }
        }

        function submitComment(comment) {
            jQuery(document).ready(function($){
                var postId = blogIds[index];
                $.ajax({
                    url:ajaxurl,
                    data:{
                        'action':'comment_ajax_request',
                        'postID' :postId,
                        'comment':comment
                    },
                    success:function(data){
                    },
                    error:function(errorThrown){
                        window.alert("errorThrown");
                    }
                })
            })
            var blogPostId = blogIds[index];
            console.log(blogPostId);
            console.log("Comment submitted:", comment);
        }
    });
        
}
