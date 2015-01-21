<!DOCTYPE html>
<html lang="en">
	<head>
		<title>{$payment}</title>
		
		<!-- Meta, title, CSS, favicons, etc. -->
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<!-- Latest compiled and minified CSS -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">

		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
		
		<link rel="stylesheet" href="https://code.jquery.com/mobile/1.4.4/jquery.mobile-1.4.4.min.css" />
		<script src="https://code.jquery.com/jquery-1.11.1.min.js"></script>
		<script src="https://code.jquery.com/mobile/1.4.4/jquery.mobile-1.4.4.min.js"></script>
		
		<style>
			label {
				white-space: nowrap;
				display: inline;
			}
			
			.ui-bar-b {
				border: 1px solid #456f9a;
				background: #5e87b0;
				color: #fff;
				font-weight: bold;
				text-shadow: 0 -1px 1px #254f7a;
				background-image: -webkit-gradient(linear,left top,left bottom,from(#81a8ce),to(#5e87b0));
				background-image: -webkit-linear-gradient(#81a8ce,#5e87b0);
				background-image: -moz-linear-gradient(#81a8ce,#5e87b0);
				background-image: -ms-linear-gradient(#81a8ce,#5e87b0);
				background-image: -o-linear-gradient(#81a8ce,#5e87b0);
				background-image: linear-gradient(#81a8ce,#5e87b0);
			}
			
		</style>
		
	</head>
	<body>
			
			<div class="type-interior ui-page ui-body-c ui-page-active" data-role="page">
			
				<div class="ui-header ui-bar-b">
	    				<h1 class="ui-title">{$payment}</h1>
				</div>
				<div class="ui-content">
			
					$$$PAYMENT ZONE$$$
			
			
					<script>
			
			
			
					var table = $('.ncoltable2');
			
					var output = '';
			
			
					table.find('tr').each(function() {
						var row = $(this);
						var labelElement = row.find('label');
						var label = labelElement.html();
				
						if (typeof label !== 'undefined') {
							var inputElement = row.find('input[type="text"]');
							var selectElement = row.find('select');
							if (inputElement.length > 0) {
								var input = $('<div>').append(inputElement).html();
								output += '<div>' + label.replace('(mm', '') + input +  '</div>';
								inputElement.remove();
								row.hide();
							}
							else if (selectElement.length > 0) {
								var selectOutput = '';
								selectElement.each(function() {
									var selectElement = $(this);
									
									if (selectElement.attr('id') == 'Ecom_Payment_Card_ExpDate_Month') {
										selectElement.find('option[value=""]').html('{$month}');
									}
									else if (selectElement.attr('id') == 'Ecom_Payment_Card_ExpDate_Year') {
										selectElement.find('option[value=""]').html('{$year}');
									}
									
									selectElement.attr('title', '');
									
									selectOutput += '';
									selectOutput += $('<div>').append(selectElement.clone()).html();
									selectOutput += '';
									
									selectElement.remove();
								});
								output += '<div>' + label.replace('(mm', '') + '<div class="ui-controlgroup-controls">' + selectOutput + '</div></div>';
								row.hide();
							}
					
						}
					});
			
					var submitButton = table.find('input[type="submit"]');
					submitButton.attr('data-theme', 'b');
					output += $('<div>').append(submitButton.clone()).html();
					submitButton.remove();
			
					
					var cancelButton = $('.ncoltable3 input[type="button"]');
					if (cancelButton.length > 0) {
						cancelButton.each(function() {
							$(this).attr('data-theme', 'a');
							output += $('<div>').append($(this).clone()).html();
						});
						cancelButton.remove();
					}
					
					var form = $('.ncoltable3 form');
					if (form.length > 0) {
						table.parents('form').parent().append(form);
						table.parents('form').parent().append($('.ncoltable3'));
					}
					table.parent().append(output);
			
					</script>
				</div>
			</div>
	</body>
</html>