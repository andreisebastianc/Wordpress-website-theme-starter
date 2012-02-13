/**
 *
 * checks if an input field contains a valid email address
 * @param emailInputField   the field whose value is checked
 * @param emailInputLabel   the label of the input field
 */
function isValidEmail(emailInputField,emailInputLabel){
    var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;

    if(emailInputField.val() === '') {
        emailInputLabel.addClass('error');
        return false;
    }
    else if(!emailReg.test(emailInputField.val())) {
        emailInputLabel.addClass('error');
        return false;
    }
    emailInputLabel.removeClass('error');
    emailInputLabel.addClass('good');
    return true;
}

/**
 *
 * @return boolean has errors?
 *
 * Example of way to build array:
 var array=[];
 array[0] = {check : $('#message-me-form #name'),label : $('#message-me-form .name-label')};
 array[1] = {check : $('#message-me-form #message'),label : $('#message-me-form .message-label')};
 */
function validateFields(arrayOfItemsToCheck){
    var hasError = false;
    var i;
    for (i=0; i<arrayOfItemsToCheck.length; i++) {
        if(arrayOfItemsToCheck[i].check.val() === ''){
            arrayOfItemsToCheck[i].display.removeClass('good').addClass('error');
            hasError = true;
        }
        else{
            if(arrayOfItemsToCheck[i].email){
                hasError = !isValidEmail(arrayOfItemsToCheck[i].check,arrayOfItemsToCheck[i].display);
            }
            else{
                arrayOfItemsToCheck[i].display.removeClass('error').addClass('good');
            }
        }
    }
    return hasError;
}

/**
 *
 *
 * Example of delegating this action to hide labels of input fields
 $('#message-me-form').delegate('#name, #email, #message','focus blur',hideInsideLabel);
 */
function hideInsideLabel(event){
    if(event.type === 'focus' || event.type === 'focusin'){
        if($(this).val() === ''){
            $(this).parent().children('label').addClass('focused');
        }
    }
    if(event.type === 'blur' || event.type === 'focusout'){
        if($(this).val() === ''){
            $(this).parent().children('label').removeClass('focused');
            $(this).parent().children('label').show();
        }
    }

    if(event.type === 'change'){
        if($(this).val() !== ''){
            $(this).parent().children('label').hide();
        }
        else{
            $(this).parent().children('label').show();
        }
    }
}

/**
 *
 */
function fixInputWithLabel(arrayOfItemsToCheck){
    var i;
    for (i=0; i<arrayOfItemsToCheck.length; i++) {
        if(arrayOfItemsToCheck[i].check.val() !== ''){
            arrayOfItemsToCheck[i].check.parent().children('label').hide();
            arrayOfItemsToCheck[i].display.addClass('good');
        }
    }
}

/**
 *
 */
function clearInputFields(arrayOfItemsToCheck){
    var i;
    for (i=0; i<arrayOfItemsToCheck.length; i++) {
        arrayOfItemsToCheck[i].check.parent().children('label').show(400).removeClass('focused');
        arrayOfItemsToCheck[i].check.val('');
        arrayOfItemsToCheck[i].display.removeClass('good');
    }
}

/**
 *
 * @TODO finish
 * @TODO callbacks required
 */
function hideAndRemove(element){
    element.hide(1000,function(){
        element.remove();
    });
}

/**
 *
 */
function toggleCollapse(element){
    element = $.data(this,'element');
    $(element).animate({
        height: 'toggle'
    },500,function(){
        $(element).parent().toggleClass('minimized');
    });
}
