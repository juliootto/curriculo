/*!
* Start Bootstrap - Resume v7.0.6 (https://startbootstrap.com/theme/resume)
* Copyright 2013-2023 Start Bootstrap
* Licensed under MIT (https://github.com/StartBootstrap/startbootstrap-resume/blob/master/LICENSE)
*/
//
// Scripts
// 
async function escondeSubmitMessage() {
    await new Promise(resolve => setTimeout(resolve, 2500));
    document.getElementById("submitSuccessMessage").setAttribute("class", "d-none")
    document.getElementById("submitErrorMessage").setAttribute("class", "d-none")
  }

function formataFone(f)
    {
        
        var str = f.value;
        str=str.replace("(","");
        str=str.replace(")","");
        str=str.replace("-","");
        str=str.replace(" ","");
        //alert(str.length);
        if(str.length>=11){
            f.value = "("+str.slice(0,2)+") "+str.slice(2,7)+"-"+str.slice(7);
        }
        else if(str.length<11 && str.length>=5){
            f.value = "("+str.slice(0,2)+") "+str.slice(2,6)+"-"+str.slice(6);
        }
        else{
            f.value = "("+str.slice(0,2)+") "+str.slice(2);
        };
    }

window.addEventListener('DOMContentLoaded', event => {

    // Activate Bootstrap scrollspy on the main nav element
    const sideNav = document.body.querySelector('#sideNav');
    if (sideNav) {
        new bootstrap.ScrollSpy(document.body, {
            target: '#sideNav',
            rootMargin: '0px 0px -40%',
        });
    };

    // Collapse responsive navbar when toggler is visible
    const navbarToggler = document.body.querySelector('.navbar-toggler');
    const responsiveNavItems = [].slice.call(
        document.querySelectorAll('#navbarResponsive .nav-link')
    );
    responsiveNavItems.map(function (responsiveNavItem) {
        responsiveNavItem.addEventListener('click', () => {
            if (window.getComputedStyle(navbarToggler).display !== 'none') {
                navbarToggler.click();
            }
        });
    });

    $('#contactForm').submit(function(e) {
        e.preventDefault(); // prevent from submitting form directly
   
        $.ajax({
        url: 'php/contato.php',
        method: 'post',
        data: $("#contactForm").serializeArray() // convert all form data to array (key:value)
        })
        .done(function(response){
            document.getElementById("submitSuccessMessage").setAttribute("class", "");// show the response
            document.getElementById("contactForm").reset();   
            escondeSubmitMessage();         
        })
        .fail(function(error){
            document.getElementById("submitErrorMessage").setAttribute("class", "");// show the response; // show the error.   
            escondeSubmitMessage();         
        });
    }
    );

});



