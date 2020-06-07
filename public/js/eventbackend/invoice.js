$(document).ready(function() {


$('.add').click(function () {
		if ($(this).prev().val() < 3) {
    $(this).prev().val(+$(this).prev().val() + 1).val();
		}
});
$('.sub').click(function () {
		if ($(this).next().val() > 0) {
    	if ($(this).next().val() > 0) $(this).next().val(+$(this).next().val() - 1);
		}
});


/* Set rates + misc */
var taxRate = messages.tax_rate;
var fadeTime = 100;


/* Assign actions */
$('.add,.sub').on('click',function () {
    var totalval = 0;
    var itemArr = [];
    var priceArr = [];
    $('.items').each(function () {
          itemArr.push( parseInt($(this).val()));
    });
    var itemAd = itemArr.reduce((a, b) => a + b, 0)
    $('#totalItem').text(itemAd);
    $('.product-price').each(function () {
         priceArr.push( parseFloat($(this).text()));
    });
    var totalAmt = [];
    priceArr =  priceArr.filter(Boolean);
    itemArr =  itemArr.filter(Boolean);
    $.each( priceArr, function( index, value ){
      var priceAmt = parseFloat(value);
      var itemCount = itemArr[index];
      var priceAmt = currency(priceAmt);
      var totalval = priceAmt.multiply(itemCount);
                        totalAmt.push(totalval);
    });
    var totalval = totalAmt.reduce((a, b) => a + b, 0)
    $('#totalItem').text(itemAd);
    updateQuantity(this);
});

/* Recalculate cart */
function recalculateCart()
{
    var subtotal = 0;

    /* Sum up row totals */
    $('.product').each(function () {
        subtotal += parseFloat($(this).children('.product-line-price').text());
    });
    /* Calculate totals */
    var subtotalval = currency(subtotal);
    var tax = 0;
    if(messages.ownedBy==2){
    var tax = subtotalval.multiply(taxRate).divide(100);
    }
    var total = subtotalval.add(tax);
    /* Update totals display */
    $('.totals-value').fadeOut(fadeTime, function () {
        $('#cart-subtotal').text(subtotal);
        $('#cart-tax').text(tax);
        $('#cart-total').text(total);
        if (total == 0) {
            $('.checkout').fadeOut(fadeTime);
        } else {
            $('.checkout').fadeIn(fadeTime);
        }
        $('.totals-value').fadeIn(fadeTime);
    });
}


/* Update quantity */
function updateQuantity(quantityInput)
{
    var productRow = $(quantityInput);
    var x = $(quantityInput).attr('class');
    if(x=='sub'){
        var quantity = productRow.parent().parent().find('.items').val();  
    }
    else if(x=='add'){
        var quantity = productRow.prev('.items').val();
    }
    /* Calculate line price */
    var price = parseFloat(productRow.parent().parent().prev('.product-price').text());
    var linePrice = price * quantity;
    /* Update line price display and recalc cart totals */
    productRow.parent().parent().next('.product-line-price').each(function () {
        $(this).fadeOut(fadeTime, function () {
            $(this).text(linePrice.toFixed(2));
            recalculateCart();
            $(this).fadeIn(fadeTime);
        });
    });
}



});

var form = document.getElementById("book_now_form");

document.getElementById("continue").addEventListener("click", function () {
  form.submit();
});