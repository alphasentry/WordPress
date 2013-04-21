<?php
	// Deny direct access to this page
    if (defined('ALLOW_INCLUDE') === false)
        die('no direct access');
    
    // Get credits remaining to display on page
	$creditsstring = $this->get_credits();
	$credits = explode(',', $creditsstring);
	$freecredits = 'N/A';
	$paidcredits = 'N/A';
	if(count($credits) > 0)
	{
	  	$freecredits = number_format($credits[0]);
	   	$paidcredits = number_format($credits[1]);
	}
	
	// Generate nonce
	$alphasentry_nonce = wp_create_nonce('alphasentry_ajax_nonce');
?>
<script type="text/javascript">
// Remove Item from GreyList and remove from table
function alphasentry_RemoveItem(ItemId, ListName, Expires)
{
	// Build query string
	var PostData = 'action=alphasentry_ajax&alphasentry_nonce=<? echo $alphasentry_nonce; ?>&alphasentry_action=RemoveItem&alphasentry_itemId=' + escape(ItemId) + '&alphasentry_listName=' + escape(ListName) + '&alphasentry_expires=' + escape(Expires);
	// Initialize AJAX request object
	var Request = new alphasentry_AjaxRequest();
	Request.ItemId = ItemId;
	Request.ListName = ListName;
	Request.Expires = Expires;
	
	// Set tasks after AJAX request is complete
	Request.onreadystatechange = function()
	{
		if (Request.readyState == 4)
		{
			if (Request.status == 200 || window.location.href.indexOf('http') == -1)
			{
				// Process response
				var responseData = Request.responseText.split(',');
				// Update credit counts
				alphasentry_UpdateCredits(responseData[1], responseData[2]);

				// If it's successful
				if(responseData[0] == '1')
				{
					// Remove from items table
					if(document.getElementById('alphasentry_BrowseItems_Row' + Request.ListName + '-' + Request.ItemId + '-' + Request.Expires))
						document.getElementById('alphasentry_BrowseItems_Row' + Request.ListName + '-' + Request.ItemId + '-' + Request.Expires).parentNode.removeChild(document.getElementById('alphasentry_BrowseItems_Row' + Request.ListName + '-' + Request.ItemId + '-' + Request.Expires));
				}
			}
			else
			{
				//
			}
		}
	};
	// Post AJAX request
	Request.open('POST', '<?php echo admin_url('admin-ajax.php'); ?>', true);
	Request.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	Request.send(PostData);
}

// Hide an item by DOM ID if it exists
function alphasentry_HideById(ElementId)
{
	if(document.getElementById(ElementId))
	{
		document.getElementById(ElementId).style.visibility = 'hidden';
		document.getElementById(ElementId).style.display = 'none';
	}
}

// Show an item by DOM ID if it exists
function alphasentry_ShowById(ElementId)
{
	if(document.getElementById(ElementId))
	{
		document.getElementById(ElementId).style.visibility = 'visible';
		document.getElementById(ElementId).style.display = 'inline';
	}
}

// Flag a transaction using the transactions table
function alphasentry_FlagTransaction(TransactionId)
{
	// Build query string
	var PostData = 'action=alphasentry_ajax&alphasentry_nonce=<? echo $alphasentry_nonce; ?>&alphasentry_action=FlagTransaction&alphasentry_transactionId=' + escape(TransactionId);

	// Set item to pending button
	if(document.getElementById('alphasentry_Transactions_' + TransactionId + '_Flag'))
		document.getElementById('alphasentry_Transactions_' + TransactionId + '_Flag').innerHTML = '<button class="button" onclick="alphasentry_UnflagTransaction(\'' + TransactionId + '\');"><? _e('Flagging...', 'alphasentry'); ?></button>';
	
	// Set tasks after AJAX request is complete
	var Request = new alphasentry_AjaxRequest();
	Request.TransactionId = TransactionId;
	
	Request.onreadystatechange = function()
	{
		if (Request.readyState == 4)
		{
			if (Request.status == 200 || window.location.href.indexOf('http') == -1)
			{
				var responseData = Request.responseText.split(',');
				// Update credit values
				alphasentry_UpdateCredits(responseData[1], responseData[2]);

				// If flagging was successful...
				if(responseData[0] == '1')
				{
					// Change item button
					if(document.getElementById('alphasentry_Transactions_' + Request.TransactionId + '_Flag'))
						document.getElementById('alphasentry_Transactions_' + Request.TransactionId + '_Flag').innerHTML = '<button class="button" onclick="alphasentry_UnflagTransaction(\'' + Request.TransactionId + '\');"><? _e('Unflag', 'alphasentry'); ?></button>';
				}
			}
			else
			{
				//
			}
		}
	};

	// Post AJAX request
	Request.open('POST', '<?php echo admin_url('admin-ajax.php'); ?>', true);
	Request.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	Request.send(PostData);
}

// Unflag a transaction
function alphasentry_UnflagTransaction(TransactionId)
{
	// Build query string
	var PostData = 'action=alphasentry_ajax&alphasentry_nonce=<? echo $alphasentry_nonce; ?>&alphasentry_action=UnflagTransaction&alphasentry_transactionId=' + escape(TransactionId);
	// Set button pending
	if(document.getElementById('alphasentry_Transactions_' + TransactionId + '_Flag'))
		document.getElementById('alphasentry_Transactions_' + TransactionId + '_Flag').innerHTML = '<button class="button" onclick="alphasentry_FlagTransaction(\'' + TransactionId + '\');"><? _e('Unflagging...', 'alphasentry'); ?></button>';
	
	// Set tasks after AJAX request is complete
	var Request = new alphasentry_AjaxRequest();
	Request.TransactionId = TransactionId;
	Request.onreadystatechange = function()
	{
		if (Request.readyState == 4)
		{
			if (Request.status == 200 || window.location.href.indexOf('http') == -1)
			{
				var responseData = Request.responseText.split(',');
				
				// Update credits available
				alphasentry_UpdateCredits(responseData[1], responseData[2]);
				
				// If flagging was successful...
				if(responseData[0] == '1')
				{
					// Update flag/unflag button
					if(document.getElementById('alphasentry_Transactions_' + Request.TransactionId + '_Flag'))
						document.getElementById('alphasentry_Transactions_' + Request.TransactionId + '_Flag').innerHTML = '<button class="button" onclick="alphasentry_FlagTransaction(\'' + Request.TransactionId + '\');"><? _e('Flag', 'alphasentry'); ?></button>';
				}
			}
			else
			{
				//
			}
		}
	};

	// Post AJAX request
	Request.open('POST', '<?php echo admin_url('admin-ajax.php'); ?>', true);
	Request.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	Request.send(PostData);
}

// Delete a transaction
function alphasentry_DeleteTransaction(TransactionId)
{
	// Build query string
	var PostData = 'action=alphasentry_ajax&alphasentry_nonce=<? echo $alphasentry_nonce; ?>&alphasentry_action=DeleteTransaction&alphasentry_transactionId=' + escape(TransactionId);
	
	var Request = new alphasentry_AjaxRequest();
	Request.TransactionId = TransactionId;
	// Set tasks after AJAX request is complete
	Request.onreadystatechange = function()
	{
		if (Request.readyState == 4)
		{
			if (Request.status == 200 || window.location.href.indexOf('http') == -1)
			{
				// Update credits available
				var responseData = Request.responseText.split(',');
				alphasentry_UpdateCredits(responseData[1], responseData[2]);
				
				// If delete was successful...
				if(responseData[0] == '1')
				{
					// Remove transaction from transactions table
					if(document.getElementById('alphasentry_Transactions_row_' + Request.TransactionId))
						document.getElementById('alphasentry_Transactions_row_' + Request.TransactionId).parentNode.removeChild(document.getElementById('alphasentry_Transactions_row_' + Request.TransactionId));
				}
			}
			else
			{
				//
			}
		}
	};
	// Post AJAX request
	Request.open('POST', '<?php echo admin_url('admin-ajax.php'); ?>', true);
	Request.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	Request.send(PostData);
}

// Update the Free and Paid credits values
function alphasentry_UpdateCredits(FreeCredits, PaidCredits)
{
	if(document.getElementById('alphasentry_status_value_FreeCredits'))
		document.getElementById('alphasentry_status_value_FreeCredits').innerHTML = FreeCredits;
	if(document.getElementById('alphasentry_status_value_PaidCredits'))
		document.getElementById('alphasentry_status_value_PaidCredits').innerHTML = PaidCredits;
}

// Hide a DOM element by ID, if it exists
function alphasentry_HideById(ElementId)
{
	if(document.getElementById(ElementId))
	{
		document.getElementById(ElementId).style.visibility = 'hidden';
		document.getElementById(ElementId).style.display = 'none';
	}
}

// Show a DOM element by ID, if it exists
function alphasentry_ShowById(ElementId)
{
	if(document.getElementById(ElementId))
	{
		document.getElementById(ElementId).style.visibility = 'visible';
		document.getElementById(ElementId).style.display = 'inline';
	}
}

// Define AJAX request
function alphasentry_AjaxRequest()
{
	var activexmodes = ['Msxml2.XMLHTTP', 'Microsoft.XMLHTTP'];
	var TransactionId = '';
	var ItemId = '';
	var ListName = '';
	var Expires = '';
	
	if (window.ActiveXObject)
	{
		for (var i = 0; i < activexmodes.length; i++)
		{
			try
			{
				return new ActiveXObject(activexmodes[i]);
			}
			catch(e)
			{
			}
		}
	}
	else if (window.XMLHttpRequest)
		return new XMLHttpRequest();
	else
		return false;
}
</script>
<div class="wrap">
	<!-- Page header -->
	<div id="icon-edit-pages" class="icon32 icon32-posts-page"><br /></div><h2><?php _e('AlphaSentry Data', 'alphasentry'); ?></h2>
	<!-- Data from Plugin -->
	<?php $this->get_data();?>
</div>