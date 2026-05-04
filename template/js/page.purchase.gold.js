//Purchase Gold Page scripts
var CURRENCY_SILVER = 1;
var CURRENCY_GOLD = 2;
		
var PurchaseGold =
{
	RealmId: 1,
	
	AmountUpdateTimeout: null,
	
	BindHandlers: function()
	{
		$('#gold-amount').keypress(function(theEvent)
		{
			var key = theEvent.keyCode || theEvent.which;
			key = String.fromCharCode(key);
			var regex = /[0-9]|\./;
			
			if (!regex.test(key))
			{
				theEvent.returnValue = false;
				if (theEvent.preventDefault)
					theEvent.preventDefault();
			}
		});
		
		//Cost Calculations
		$('#gold-amount').keyup(function(e)
		{
			if (PurchaseGold.AmountUpdateTimeout != null)
				clearTimeout(PurchaseGold.AmountUpdateTimeout);
				
			//Calculate the cost
			var amount = parseInt($(this).val());
			//get the left overs
			var unit = parseInt($('#gold-amount').attr('data-unit')) || 1000;
			var rate = parseInt($('#gold-amount').attr('data-rate')) || 1;
			var min = parseInt($('#gold-amount').attr('data-min')) || unit;
			var max = parseInt($('#gold-amount').attr('data-max')) || 100000;
			if (isNaN(amount) || amount < min) amount = min;
			var leftOver = amount % unit;
			
			//any left over costs +1 gold coin
			if (leftOver > 0)
			{
				amount -= leftOver;
				amount += unit;
				
				PurchaseGold.AmountUpdateTimeout = setTimeout(function(){ $('#gold-amount').val(amount); }, 700);
			}
			
			//gold limit from admin
			if (amount > max)
			{
				amount = max;
				PurchaseGold.AmountUpdateTimeout = setTimeout(function(){ $('#gold-amount').val(amount); }, 700);
			}
			
			//calculate the price
			var price = Math.ceil(amount / unit) * rate;
			
			//update the price
			$('#cost-amount').html(price);
			$('#gold-amount').attr('data-price', price);
		});
		
		//Form Completion
		$('#gold-complete-form').on('submit', function()
		{
			var charValue = $('#character-select').find('option:selected').val();
			
			if (charValue.length == 0 || charValue == '')
			{
				//no characters, fail
				$.fn.WarcryAlertBox('open', '<p>Unable to proceed, please select character.</p>');
				return false;
			}
			
			//Get the price value
			var $Price = $('#gold-amount').attr('data-price');
			
			//check if the server is online... Im gonna do that in verifyPayment
			$.get($BaseURL + "/ajax.php?phase=4",
			{
				silver: 0,
				gold: $Price,
				realm: PurchaseGold.RealmId
			},
			function(data)
			{
				if (data == 'OK')
				{
					$('#gold-complete-form').off('submit');
					$('#gold-complete-form').submit();
				}
				else
				{
					//prompt the error
					$.fn.WarcryAlertBox('open', '<p>' + data + '</p>');
				}
			});
			
			return false;
		});
	}
};

//Initialize
$(function()
{
	PurchaseGold.BindHandlers();
});