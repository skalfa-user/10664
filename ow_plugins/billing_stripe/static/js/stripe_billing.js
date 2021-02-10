// init Stripe
var stripe = Stripe(stripeParams.publicKey);

// create and mount Stripe.elements
var elements = stripe.elements();

// create and Stripe.Elements
var cardElement = elements.create('card',
    { classes: { base: 'ow_photo_upload_description stripe_card'},
        style: {
            base: {
                color: stripeParams.fontColor,
                fontFamily: stripeParams.fontFamily,
                iconColor: stripeParams.iconColor,
                fontSmoothing: 'antialiased',
                fontSize: stripeParams.fontSize,
                '::placeholder': {
                    color: stripeParams.placeholderColor,
                }
            },
            invalid: {
                color: stripeParams.errorFontColor,
                iconColor: stripeParams.errorIconColor
            }
        },
        hidePostalCode: true
    });

cardElement.mount('#card-element');


// get submit button
var cardButton = document.getElementById('card-button');

// listen event click submit button
cardButton.addEventListener('click', function() {
    // check cardholder name
    var cname = $(".c-name").val();
    if ( !cname.length ) {
        OW.error(OW.getLanguageText('billingstripe', 'name_on_card_required'));
        $(".c-name", $(this)).focus();
        return false;
    }

    // check required fields
    if (stripeParams.requireData) {
        // country
        var b_country = $(".billing-country").val();
        if ( !b_country.length )
        {
            OW.error(OW.getLanguageText('billingstripe', 'country_required'));
            return false;
        }

        // state
        var b_state = $(".billing-state").val();
        if ( !b_state.length )
        {
            OW.error(OW.getLanguageText('billingstripe', 'state_required'));
            return false;
        }

        // address1
        var b_address = $(".billing-address1").val();
        if ( !b_address.length )
        {
            OW.error(OW.getLanguageText('billingstripe', 'address_required'));
            return false;
        }

        var b_zip = $(".billing-zip").val();
        if ( !b_zip.length )
        {
            OW.error(OW.getLanguageText('billingstripe', 'zip_code_required'));
            return false;
        }
    }

    // collect token data
    var tokenData = {name: cname};

    if (stripeParams.requireData) {
        tokenData = {
            name: cname,
            address_country: b_country,
            address_line1: b_address,
            address_zip: b_zip,
            address_state: b_state
        }
    }

    // collect billing details
    var billingDetails = { name: cname };

    OW.inProgressNode(cardButton);

    // create token
    stripe.createToken(cardElement, tokenData).then(function (result) {
        if (result.error) {
            // display error.message
            showStripeError(result.error);
            return false;
        }
        else {
            OW.info(OW.getLanguageText('billingstripe', 'notification_after_actions'));
            processSale(result.token.id, billingDetails);
        }
    });
});

function processSale(token, billingDetails) {
    if (stripeParams.recurring === 1) {
        $.ajax({
            url: stripeParams.processSale,
            type: 'POST',
            data: {token: token},
            dataType: 'json',
            success: function( response ) {
                processResponse(response);
            }
        })
    }
    else {
        stripe.createPaymentMethod('card', cardElement, {billing_details: billingDetails}).then(function(result) {
            if (result.error) {
                showStripeError(result.error);
            } else {
                $.ajax({
                    url: stripeParams.processSale,
                    type: 'POST',
                    data: {token: token, paymentMethodId: result.paymentMethod.id},
                    dataType: 'json',
                    success: function( response ) {
                        processResponse(response);
                    }
                })
            }
        });
    }
}

function processResponse(response) {
    // check error
    if (response.status === 'error') {
        var form = $('#' + stripeParams.formId);
        form.append("<input type=\"hidden\" name=\"status\" value='error' />");
        form.append("<input type=\"hidden\" name=\"message\" value=\"" + response.message + "\" />");
        form.get(0).submit();
    }

    // send success status
    if (response.status === 'success') {
        var form = $('#' + stripeParams.formId);
        form.append("<input type=\"hidden\" name=\"status\" value='success' />");
        form.append("<input type=\"hidden\" name=\"redirect\"  value=\"" + response.redirect + "\" />");
        form.get(0).submit();
    }

    // process subscription requires action
    if (response.status === 'subscription_requires_action') {
        stripe.handleCardPayment(
            response.payment_intent_client_secret).then(function(result) {
            // process error
            if (result.error) {
                // display error.message
                showStripeError(result.error);
                return false;
            }
            else {
                OW.info(OW.getLanguageText('billingstripe', 'notification_after_actions'));
                // payment has succeeded
                $.ajax({
                    url: stripeParams.processSale,
                    type: 'POST',
                    data: {
                        status: 'subscription_payment_success',
                        paymentId: result.paymentIntent.id,
                        subscriptionId: response.subscriptionId
                    },
                    dataType: 'json',
                    success: function( response ) {
                        processResponse(response);
                    }
                })
            }
        });
    }

    // process payment requires action
    if (response.status === 'payment_requires_action') {
        stripe.handleCardAction(
            response.payment_intent_client_secret).then(function(result) {
            // process error
            if (result.error) {
                // display error.message
                showStripeError(result.error);
                return false;
            }
            else {
                OW.info(OW.getLanguageText('billingstripe', 'notification_after_actions'));
                // payment has succeeded
                $.ajax({
                    url: stripeParams.processSale,
                    type: 'POST',
                    data: { status: 'payment_success', paymentId: result.paymentIntent.id},
                    dataType: 'json',
                    success: function( response ) {
                        processResponse(response);
                    }
                });
            }
        });
    }
}

function showStripeError(error) {
    OW.activateNode(cardButton);

    switch(error.code) {
        case 'incomplete_number':
            OW.error(OW.getLanguageText('billingstripe', 'card_number_invalid'));
            break;
        case 'incomplete_expiry':
            OW.error(OW.getLanguageText('billingstripe', 'exp_date_invalid'));
            break;
        case 'incomplete_cvc':
            OW.error(OW.getLanguageText('billingstripe', 'cvc_invalid'));
            break;
        default:
            OW.error(error.message);
            break;
    }
}
