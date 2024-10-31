/**
 * File: Script.js
 *
 * Description: All ajax call and js write here.
 *
 * @package my-live-cart
 * @version 1.0.0
 */

jQuery(document).ready(
	function () {
		jQuery(".zt-loader").hide();
		jQuery("#auth_form i.dashicons").on(
			"click", function () {
				if (jQuery(this).hasClass("dashicons-hidden")) {
					jQuery(this).removeClass("dashicons-hidden").addClass("dashicons-visibility");
					jQuery("#auth_key").attr("type", "text");
				} else {
					jQuery(this).removeClass("dashicons-visibility").addClass("dashicons-hidden");
					jQuery("#auth_key").attr("type", "password");
				}
			});

		function check_event_status() {
			var event_key = jQuery("#event_detail_embed").attr("event-key");
			var ajaxurl = jQuery(".high_light_product").attr("data-action-url");
			jQuery.ajax({
				url: ajaxurl,
				data: {
					action: "check_event_status",
					event_key: event_key,
					nonce: ztcbl_qv.nonce,
				},
				type: "POST",
				success: function (data) {
					if (data === "live" || data === "completed") {

						if (jQuery(window).width() < 1024) {
							jQuery('.show_mobile_view').show();
						}
						jQuery(".product_detail_section").show();
						jQuery(".event_detail_embed").css('width', 'calc(100% - 363px)');
					}
				},
				complete: function (data) {
					if (data.responseText !== "live" && data.responseText !== "completed") {
						setTimeout(check_event_status, 1000);
					}
				},
			});
		}

		function fetch_high_light_product_on_load() {
			let secret_key = jQuery('.high_light_product').attr('random-key');
			var event_id = jQuery(".high_light_product").attr("event-key");
			let myHeaders = new Headers();
			//let formdata = '';
			myHeaders.append("secret-key", secret_key);
			myHeaders.append("api-type", "1");

			var requestOptions = {
				method: 'GET',
				headers: myHeaders,
				redirect: 'follow'
			};

			fetch(`${ztcbl_qv.ztcbl_api_url}event/${event_id}/highlighted/product`, requestOptions)
				.then(response => response.json()) // Parse the response as JSON
				.then((result) => {
					if (result.data != 0) {
						jQuery('.hide_product_card').each(function () {
							var product_id = jQuery(this).attr('product_id');
							let event_id = jQuery(this).attr('event_id');

							if (result.data == product_id) {
								//jQuery(".high_light_product").show();
								jQuery(this).show();
								jQuery('.highlight_product_separator').show();
								jQuery(".product_list_section").css('max-height', 'calc(90% - 180px)');
							} else {
								jQuery(this).hide();
								// jQuery('.highlight_product_separator').hide();
							}

						});
					} else {
						jQuery('.hide_product_card').hide();
						jQuery('.highlight_product_separator').hide();
					}

				})
				.catch(error => {
					jQuery('.hide_product_card').hide();
					jQuery('.highlight_product_separator').hide();
				});
		}

		function fetch_high_light_product(productID, highlighValue) {
			if (highlighValue != 0) {
				jQuery('.hide_product_card').each(function () {
					var product_id = jQuery(this).attr('product_id');
					let event_id = jQuery(this).attr('event_id');

					if (productID == product_id) {
						jQuery(this).show();
						jQuery('.highlight_product_separator').show();
						jQuery(".product_list_section").css('max-height', 'calc(90% - 180px)');
					} else {
						jQuery(this).hide();
					}

				});
			} else {
				jQuery('.hide_product_card').hide();
				jQuery('.highlight_product_separator').hide();
				jQuery(".product_list_section").css('max-height', '90%');
			}
		}

		function hideProductCards() {
			// Hide all product cards
			jQuery('.hide_product_card').hide();

			// Apply CSS changes immediately
			jQuery('.highlight_product_separator').hide();
			jQuery('.product_list_section').css('max-height', '90%');
		}


		function getEventKey() {
			const urlParam = new URLSearchParams(window.location.search);
			let eventKey = urlParam.get('event_key');

			if (!eventKey) {
				const url = window.location.href;
				const keyParts = url.split('/');

				eventKey = keyParts.filter(Boolean).pop();
			}
			return eventKey;
		}

		const navigateToEListHandler = () => {
			try {
				// Construct the redirect URL safely
				if (ztcbl_qv && ztcbl_qv.ztcbl_site_url) {
					const redirectUrl = `${ztcbl_qv.ztcbl_site_url}/events-list`;

					// Optional: Log for debugging or analytics purposes
					console.log('Navigating to:', redirectUrl);

					// Perform the redirection
					window.location.href = redirectUrl;
				} else {
					console.error('ztcbl_site_url is not defined.');
				}
			} catch (error) {
				// Handle any potential errors gracefully
				console.error('Error during redirection:', error);
			} finally {
				// Optionally remove the event listener to avoid multiple redirects
				socket.off('navigateToEList', navigateToEListHandler);
			}
		};

		function connectHighlightedSocket() {
			const socket = io(`${ztcbl_qv.ztcbl_socket_url}`);
			socket.on('connect', () => {
				console.log('Connection Succesfull through wordpress');
			});

			socket.on('highlighted', (data) => {
				let eventKey = data.eventKey;
				let urlKey = getEventKey();

				if (eventKey === urlKey) {
					if (data.highlited === 'null') {
						hideProductCards();
					} else {
						let productID = data.productID;
						let highlited = data.highlited;
						fetch_high_light_product(productID, highlited);
					}

				}
			});


			socket.on('navigateToEList', navigateToEListHandler);


			socket.on('disconnect', () => {
				console.log('Disconnected from WebSocket');
			});

			socket.on('connect_error', (error) => {
				console.error('Connection error:', error);
			});

		}


		const urlParams = new URLSearchParams(window.location.search);
		if (urlParams.get('event_key')) {
			check_event_status();
			fetch_high_light_product_on_load();
			connectHighlightedSocket();
			showCartValue();
		}
		if (window.location.href.indexOf('events-detail') > -1) {
			check_event_status();
			fetch_high_light_product_on_load();
			connectHighlightedSocket();
			showCartValue();
		}

		jQuery(document).on("click", ".ztcbl-qv-button", function (e) {
			e.preventDefault();
			jQuery(this).addClass("loading");
			var ajaxurl = jQuery(".high_light_product").attr("data-action-url");
			let watcherId = localStorage.getItem('watcher_unique_id');
			var t = jQuery(this),
				product_id = t.data("product_id"),
				event_key = t.data("redirect-key"),
				event_id = t.data("redirect-id");
			jQuery.ajax({
				url: ajaxurl,
				data: {
					action: "ztcbl_load_product_quick_view",
					product_id: product_id,
					event_key: event_key,
					event_id: event_id,
					watcherId: watcherId
					// ajax_nonce: 'hfsjdsf',
				},
				type: "POST",
				success: function (response) {
					jQuery(".ztcbl-qv-button").removeClass("loading");
					window.open(response.data.url, "_blank");
				},
			});
		});

		jQuery('#mylivecart-connector').submit(function (e) {
			e.preventDefault();
			jQuery(this).addClass("loading");
			let consumer_key = jQuery('#ztcbl_consumer_key').val();
			let consumer_secret_key = jQuery('#ztcbl_consumer_secret_key').val();
			var ajaxurl = ztcbl_qv.ajaxurl;
			jQuery.ajax({
				url: ajaxurl,
				data: {
					action: "auth_key_validate",
					consumer_key: consumer_key,
					consumer_secret_key: consumer_secret_key,
					nonce: ztcbl_qv.nonce,
				},
				type: "POST",
				beforeSend: function () {
					jQuery(".zt-loader").show();
					jQuery(".auth_div").css('opacity', '0.3');
				},
				success: function (response) {
					if (response == '') {
						jQuery('.ztcbl_validate_response').html('Firstly Generate Woocoomerce Rest Api key.')
						jQuery(".zt-loader").hide();
						jQuery(".auth_div").css('opacity', '1');
					} else {
						window.open(response.data.url);
						jQuery(".zt-loader").hide();
						jQuery(".auth_div").css('opacity', '1');
					}
				},
			});
		});

		jQuery("body").on("click", ".infbtn", function () {
			var infId = jQuery(this).data("inf-id");
			jQuery("#influencer_id").val(infId);
			let url = jQuery("#url").val();
			let redirect_url = `${url}=${infId}`;
			jQuery("#redirect_url").val(redirect_url);

			jQuery(".ztcbl-modal").css("display", "block");
		});

		jQuery(".zt-dropdown-toggle,.zt-category-dropdown").on("click", function (e) {
			e.stopPropagation();
			jQuery(".zt-product-dropdown").toggle();
			jQuery("#zt-arrowDown").toggle();
			jQuery("#zt-arrowUp").toggle();
		});

		function zt_performSearch() {
			var searchTerm = jQuery(".zt-search-field").val().toLowerCase();
			var found = false;
			jQuery(".zt-product-list li").each(function () {
				var listItemText = jQuery(this).text().toLowerCase();
				if (listItemText.includes(searchTerm)) {
					jQuery(this).show();
					found = true;
				} else {
					jQuery(this).hide();
				}
			});
			jQuery(".zt-no-result").toggle(!found);
		}

		jQuery('.zt-search-field').on('input', function () {
			zt_performSearch();
		});

		var selectedProduct = [];

		// Click event handler for product list items
		jQuery(".zt-product-list li").on("click", function () {
			let offer = jQuery('.zt-product-list').attr('by-offer');
			if (offer == 'false') {
				var productID = parseInt(jQuery(this).attr("value"));
				addRemoveProduct(productID);
			}
		});

		// Function to add or remove a product from the selectedProduct array
		function addRemoveProduct(productID) {
			var index = selectedProduct.indexOf(productID);

			if (index === -1) {
				selectedProduct.push(productID);

			} else {
				selectedProduct.splice(index, 1);

			}

			// Update the UI based on the selectedProduct array
			updateSelectedProductsUI();
			updateproductlisUI();
		}

		// Function to update the UI based on the selectedProduct array
		function updateSelectedProductsUI() {
			// Update the content of the selected products container
			var selectedProductsContainer = jQuery(".zt-category-dropdown .zt-selected-product div");
			if (selectedProduct.length === 0) {
				selectedProductsContainer.html("Select Product");
			} else {
				var selectedProductNames = selectedProduct.map(function (productID) {
					var productText = jQuery(".zt-product-list li[value='" + productID + "']").text();
					return productText;
				});

				if (selectedProduct.length === 1) {
					var html = "<ul>" +
						"<li>" +
						"<span class='zt-product-name'>" + selectedProductNames[0] + "</span>" +
						"<span class='zt-close'>&times;</span>" +
						"</li>" +
						"</ul>";
					selectedProductsContainer.html(html);
				} else {
					var moreCount = selectedProduct.length - 1;
					var html = "<ul style='display:flex;'>" +
						"<li class='first-product-li'>" +
						"<span class='zt-product-name'>" + selectedProductNames[0] + "</span>" +
						"</li>" +
						"<li>" +
						"<span class='product-name'>" + moreCount + " More</span>" +
						"</li>" +
						"</ul>";
					selectedProductsContainer.html(html);
				}
			}
		}

		function updateproductlisUI() {
			jQuery(".zt-product-list li").each(function () {
				var productID = parseInt(jQuery(this).attr("value"));
				if (selectedProduct.includes(productID)) {
					jQuery(this).addClass("selected");
				} else {
					jQuery(this).removeClass("selected");
				}
			});
		}

		jQuery(".zt-category-dropdown .zt-selected-product div").on("click", ".zt-close", function () {
			var productName = jQuery(this).siblings(".zt-product-name").text().trim();

			var productID = getProductIDByName(productName);
			addRemoveProduct(productID);
		});

		// Function to get product ID based on product name
		function getProductIDByName(productName) {
			var productID = -1;
			jQuery(".zt-product-list li").each(function () {
				if (jQuery(this).text().trim() === productName) {
					productID = parseInt(jQuery(this).attr("value"));
					return false; // Exit the loop
				}
			});

			return productID;
		}
		function get_product_id() {
			const liElements = document.querySelectorAll('.zt-product-list li');
			const selectedItems = Array.from(liElements).filter(item => item.classList.contains('selected'));
			const selectedValues = selectedItems.map(item => item.getAttribute('value'));
			return selectedValues;
		}

		jQuery("body").on("click", ".close", function () {
			jQuery(".ztcbl-modal").css("display", "none");
			jQuery(".zt-product-list li").each(function () {
				if (jQuery(this).hasClass('selected')) {
					jQuery(this).removeClass('selected');
				}
			});
			jQuery('.zt-selected-product div ul').remove();
			selectedProduct = [];
		});

		jQuery('#inf_offer_form_').submit(function (e) {
			e.preventDefault();
			let count = 0;
			jQuery('.zt-product-list li').each(function (index, element) {
				// Use 'element' to refer to the current element in the iteration
				if (jQuery(element).hasClass('selected')) {
					count += 1;
				}
			});
			if (count > 0) {
				count = 0;
				jQuery('#offer_submit').addClass("loading");
				var influencer_id = jQuery('#influencer_id').val();
				var store_id = jQuery('#store_name').val();
				var title = jQuery('#event_title').val();
				var description = jQuery('#event_desc').val();
				var startDate = new Date(jQuery('#event_date').val()).toLocaleDateString('en-GB');
				var startTime = new Date('2000-01-01T' + jQuery('#event_time').val() + ':00').toLocaleString('en-US', { hour: 'numeric', minute: 'numeric', hour12: true });
				var showStartDate = startDate + ' ' + startTime;
				var duration = jQuery('#event_duration').val();
				var selectedProducts = [];
				let redirect_url = jQuery('#redirect_url').val();
				const liElements = document.querySelectorAll('.zt-product-list li');
				const selectedItems = Array.from(liElements).filter(item => item.classList.contains('selected'));
				var selectedProducts = [];
				jQuery(selectedItems).each(function () {
					const productId = jQuery(this).attr('value');
					const productName = jQuery(this).html();
					// Add the selected product's ID and name to the array
					selectedProducts.push({ id: productId, name: productName });
				});
				var products = JSON.stringify(selectedProducts);
				var ajaxurl = ztcbl_qv.ajaxurl;
				jQuery.ajax({
					url: ajaxurl,
					data: {
						action: "offer_influencer",
						influencer_id: influencer_id,
						store_id: store_id,
						title: title,
						description: description,
						showStartDate: showStartDate,
						duration: duration,
						products: products,
						nonce: ztcbl_qv.nonce,
					},
					type: "POST",
					success: function (response) {
						document.getElementById("inf_offer_form_").reset();
						jQuery('.zt-selected-product div').children().remove();
						jQuery(".zt-product-list li").each(function () {
							if (jQuery(this).hasClass('selected')) {
								jQuery(this).removeClass('selected');
							}
						});
						if (response == 'OK') {
							jQuery('#response_message').html('Offer is created');
							jQuery('#response_message').html('');
							jQuery(".zt-product-list li").each(function () {
								if (jQuery(this).hasClass('selected')) {
									jQuery(this).removeClass('selected');
								}
							});
							jQuery('.zt-selected-product div ul').remove();
							selectedProduct = [];
							jQuery(".ztcbl-modal").css("display", "none");
							window.location.href = redirect_url;
						} else {
							jQuery('#response_message').html(response);
						}

					},
				});
			} else {
				jQuery('#response_message').html('The product id field is required.');
			}
		});
		function decodeHtmlEntities(text) {
			var textarea = document.createElement("textarea");
			textarea.innerHTML = text;
			return textarea.value;
		}
		jQuery("body").on("click", ".update-deshicon", function () {
			jQuery('#update-deshicon').addClass('loading');
			var offerId = jQuery(this).data("offer-id");
			let url = jQuery("#url").val();
			jQuery('#offer_id').val(offerId);
			var ajaxurl = ztcbl_qv.ajaxurl;
			jQuery.ajax({
				url: ajaxurl,
				data: {
					action: "get_offer_details",
					offer_id: offerId,
					nonce: ztcbl_qv.nonce,
				},
				type: "POST",
				beforeSend: function () {
					jQuery(".zt-loader").show();
					jQuery("#ztcbl_inf_table").css('opacity', '0.3');
				},
				success: function (response) {
					jQuery('#update-deshicon').removeClass('loading');
					jQuery('.spinner.is-active').remove();
					if (response != '') {
						response = decodeHtmlEntities(response);
						var response = JSON.parse(response);
						var inf_id = response.data[0].influencer_id;
						var title = response.data[0].title;
						var Discription = response.data[0].description;
						var products = response.data[0].products;
						var datetimeString = response.data[0].start_time;
						var duration = response.data[0].duration;
						jQuery('#inf_offer_update #influencer_id').val(inf_id);
						jQuery('#inf_offer_update #event_title').val(title);
						jQuery('#inf_offer_update #event_desc').val(Discription);
						jQuery('#inf_offer_update #event_duration').val(duration);
						products.forEach(product => {
							let product_id = product.product_id;
							addRemoveProduct(product_id);
						});

						// Split the datetime string into date and time components
						var datetimeParts = datetimeString.split(' ');
						var datePart = datetimeParts[0];
						var timePart = datetimeParts[1] + ' ' + datetimeParts[2];

						// Assuming you have date and time input elements with IDs "dateInput" and "timeInput" respectively
						var dateInput = document.querySelector('#inf_offer_update #event_date');
						var timeInput = document.querySelector('#inf_offer_update #event_time');

						// Set the value of the date input element
						dateInput.value = formatDate(datePart);

						// Set the value of the time input element
						timeInput.value = formatTime(timePart);

						// Function to format the date part in the format expected by the date input element (YYYY-MM-DD)
						function formatDate(dateString) {
							var parts = dateString.split('/');
							var day = parts[0];
							var month = parts[1];
							var year = parts[2];

							// Return the formatted date string
							return year + '-' + month + '-' + day;
						}

						// Function to format the time part in the format expected by the time input element (HH:MM)
						function formatTime(timeString) {
							// Split the time and period (AM/PM)
							var timeParts = timeString.split(' ');
							var time = timeParts[0];
							var period = timeParts[1];

							// Split the time into hours and minutes
							var timeComponents = time.split(':');
							var hour = parseInt(timeComponents[0]);
							var minute = timeComponents[1];

							// Convert the hour to 24-hour format if necessary
							if (period === 'PM' && hour !== 12) {
								hour += 12;
							} else if (period === 'AM' && hour === 12) {
								hour = 0;
							}

							// Return the formatted time string
							return padZero(hour) + ':' + minute;
						}
						jQuery(".zt-loader").hide();
						jQuery("#ztcbl_inf_table").css('opacity', '1');

						// Function to pad single-digit numbers with a leading zero
						function padZero(number) {
							return number < 10 ? '0' + number : number;
						}

						// jQuery('.ztcbl-modal').load('.ztcbl-modal');
						jQuery(".ztcbl-modal").css("display", "block");
					}
					let redirect_url = `${url}=${inf_id}`;
					jQuery("#redirect_url").val(redirect_url);
				},
			});
		});

		jQuery('#inf_offer_update').submit(function (e) {

			e.preventDefault();
			let count = 0;
			jQuery('.zt-product-list li').each(function (index, element) {
				// Use 'element' to refer to the current element in the iteration
				if (jQuery(element).hasClass('selected')) {
					count += 1;
				}
			});
			if (count > 0) {
				count = 0;
				var influencer_id = jQuery('#influencer_id').val();
				var offer_id = jQuery('#offer_id').val();
				var store_id = jQuery('#store_name').val();
				var title = jQuery('#event_title').val();
				var description = jQuery('#event_desc').val();
				var startDate = new Date(jQuery('#event_date').val()).toLocaleDateString('en-GB');
				var startTime = new Date('2000-01-01T' + jQuery('#event_time').val() + ':00').toLocaleString('en-US', { hour: 'numeric', minute: 'numeric', hour12: true });
				var showStartDate = startDate + ' ' + startTime;
				var duration = jQuery('#event_duration').val();
				const liElements = document.querySelectorAll('.zt-product-list li');
				const selectedItems = Array.from(liElements).filter(item => item.classList.contains('selected'));
				var selectedProducts = [];
				jQuery(selectedItems).each(function () {
					const productId = jQuery(this).attr('value');
					const productName = jQuery(this).html();
					// Add the selected product's ID and name to the array
					selectedProducts.push({ id: productId, name: productName });
				});
				var products = JSON.stringify(selectedProducts);
				var ajaxurl = ztcbl_qv.ajaxurl;
				jQuery.ajax({
					url: ajaxurl,
					data: {
						action: "update_influencer_offer",
						influencer_id: influencer_id,
						offer_id: offer_id,
						store_id: store_id,
						title: title,
						description: description,
						showStartDate: showStartDate,
						duration: duration,
						products: products,
						nonce: ztcbl_qv.nonce,
					},
					type: "POST",
					success: function (response) {
						document.getElementById("inf_offer_update").reset();
						last_product_list_li = jQuery('.select2-selection__rendered').children().last()[0];
						jQuery('.select2-selection__rendered').children().remove();
						jQuery('.select2-selection__rendered').append(last_product_list_li);
						if (response == 'OK') {
							jQuery('#response_message').html('Offer is Updated');
							jQuery('#response_message').html('');
							jQuery(".zt-product-list li").each(function () {
								if (jQuery(this).hasClass('selected')) {
									jQuery(this).removeClass('selected');
								}
							});
							jQuery('.zt-selected-product div ul').remove();
							selectedProduct = [];
							jQuery(".ztcbl-modal").css("display", "none");
							let url = jQuery("#redirect_url").val();
							window.location.href = url;
						} else {
							jQuery('#response_message').html(response);
						}
					},

				});
			} else {
				jQuery('#response_message').html('The product id field is required.');
			}
		});

		jQuery('#zt-banner-img-span').click(function () {
			jQuery('#zt-banner-img').click();
		});

		jQuery('#zt-cover-img-span').click(function () {
			jQuery('#zt-cover-img').click();
		});

		// const urlParams = new URLSearchParams(window.location.search);
		if (urlParams.get('zt-page') && urlParams.get('e-id')) {
			event_id = urlParams.get('e-id');

			jQuery.ajax({
				url: ztcbl_qv.ajaxurl,
				type: 'POST',
				data: {
					action: "get_event_details",
					event_id: event_id,
					nonce: ztcbl_qv.nonce,
				},
				beforeSend: function () {
					jQuery(".zt-loader").show();
					jQuery(".create-event-div").hide();
				},
				success: function (response) {
					var response = decodeHtmlEntities(response);
					let r = JSON.parse(response);
					if (r.message != 'Event Details successfully') {
						jQuery('#create_response').html(r.message);
					}
					let data = r.data;
					if (Object.keys(data).length > 0) {
						jQuery('#store_title').val(data.title);
						jQuery('#store_desc').val(data.description);
						jQuery('#event_date').val(convertDateformate(data.start_date));
						jQuery('#event_time').val(convert12HourTo24Hour(data.start_times));
						jQuery('#event_date').val(convertDateformate(data.start_date));
						jQuery('#event_time').val(convert12HourTo24Hour(data.start_times));
						jQuery('#event_end_date').val(convertDateformate(data.end_date));
						jQuery('#event_end_time').val(convert12HourTo24Hour(data.end_times));
						jQuery('#zt-host-name').val(data.hosts[0].host_id);
						jQuery('#event_duration').val(data.duration);
						if (data.anonymous_chat == '1') {
							jQuery('.zt-chat-checkbox').prop('checked', true);
						} else {
							jQuery('.zt-chat-checkbox').prop('checked', false);
						}
						if (data.product_clips == '1') {
							jQuery('.zt-hightlight-checkbox').prop('checked', true);
						} else {
							jQuery('.zt-hightlight-checkbox').prop('checked', false);
						}
						let products = data.products;
						products.forEach(product => {
							let product_id = product.product_id;
							addRemoveProduct(product_id);
						});
						const imagePreview = document.getElementById('zt-banner-preview');
						imagePreview.innerHTML = '';
						if (data.file_type == "video" || data.file_type == "Video") {
							const video = document.createElement('video');
							video.src = data.banner_image;
							video.alt = 'Selected Video';
							video.controls = false;
							video.autoplay = true;
							video.muted = true;
							video.loop = true;
							video.style.maxWidth = '100%';
							imagePreview.appendChild(video);
							jQuery('#zt-banner').val(data.banner_image);
						} else {
							const img = document.createElement('img');
							img.src = data.banner_image;
							img.alt = 'Selected Image';
							img.style.maxWidth = '100%';
							imagePreview.appendChild(img);
							jQuery('#zt-banner').val(data.banner_image);
						}
						const coverimagePreview = document.getElementById('zt-cover-preview');
						coverimagePreview.innerHTML = '';
						const coverimg = document.createElement('img');
						coverimg.src = data.cover_image;
						coverimg.alt = 'Selected Image';
						coverimg.style.maxWidth = '100%';
						coverimagePreview.appendChild(coverimg);
						jQuery('#zt-cover').val(data.cover_image);
						let offer_id = data.offer_id;
						if (offer_id > 0) {
							jQuery('#event_date').attr('disabled', 'true');
							jQuery('#event_time').attr('disabled', 'true');
							jQuery("#zt-host-name").prop("disabled", true);
							jQuery('#event_duration').attr('disabled', 'true');
							jQuery('.zt-product-list').attr('by-offer', 'true');
						}
					}
					jQuery(".zt-loader").hide();
					jQuery(".create-event-div").show();
				},
				error: function (jqXHR, textStatus, errorThrown) {
					console.error(errorThrown);
				}
			});

		}
		function convertDateformate(inputDate) {
			var parts = inputDate.split('/');
			var formattedDate = parts[0] + '-' + parts[1] + '-' + parts[2];
			return formattedDate;
		}
		function convert12HourTo24Hour(time12h) {
			var [time, period] = time12h.split(' ');
			var [hours, minutes] = time.split(':');

			if (period === 'PM' && hours !== '12') {
				hours = String(Number(hours) + 12);
			} else if (period === 'AM' && hours === '12') {
				hours = '00';
			}

			return hours + ':' + minutes;
		}

		if (urlParams.get('zt-page') && urlParams.get('offer-id')) {
			var offerId = urlParams.get('offer-id');
			var ajaxurl = ztcbl_qv.ajaxurl;
			jQuery.ajax({
				url: ajaxurl,
				data: {
					action: "get_offer_details",
					offer_id: offerId,
					nonce: ztcbl_qv.nonce,
				},
				type: "POST",
				beforeSend: function () {
					jQuery(".zt-loader").show();
					jQuery(".create-event-div").hide();
				},
				success: function (response) {
					// 					console.log(response);
					if (response != '') {
						let res = JSON.parse(response);
						let data = res.data[0];
						// console.log(data);
						jQuery('#zt-offer').val('true');
						jQuery('#store_title').val(data.title);
						jQuery('#store_desc').val(data.description);
						jQuery('#event_date').val(convertDateformate(data.start_date));
						jQuery('#event_date').attr('disabled', 'true');
						jQuery('#event_time').val(convert12HourTo24Hour(data.start_times));
						jQuery('#event_time').attr('disabled', 'true');
						jQuery('#zt-host-name').val(data.influencer_id);
						jQuery("#zt-host-name").prop("disabled", true);
						jQuery('#event_duration').val(data.duration);
						jQuery('#event_duration').attr('disabled', 'true');
						jQuery('.zt-product-list').attr('by-offer', 'true');
						let products = data.products;
						products.forEach(product => {
							let product_id = product.product_id;
							addRemoveProduct(product_id);
						});
						jQuery(".zt-loader").hide();
						jQuery(".create-event-div").show();
					}
				}
			});
		}

		jQuery('#create_event_form').on('submit', function (e) {
			e.preventDefault();
			let formdata = new FormData();
			let startDate = new Date(jQuery('#event_date').val()).toLocaleDateString('en-GB');
			let startTime = new Date('2000-01-01T' + jQuery('#event_time').val() + ':00').toLocaleString('en-US', { hour: 'numeric', minute: 'numeric', hour12: true });
			let showStartDate = startDate + ' ' + startTime;
			formdata.append("title", jQuery('#store_title').val());
			formdata.append("description", jQuery('#store_desc').val());
			formdata.append("start_time", showStartDate);
			formdata.append("banner_image", jQuery('#zt-banner-img')[0].files[0]);
			formdata.append("show_public", "0");
			formdata.append("anonymous_chat", jQuery('.zt-chat-checkbox').prop('checked') ? '1' : '0');
			formdata.append("product_clips", jQuery('.zt-hightlight-checkbox').prop('checked') ? '1' : '0');
			formdata.append("system_message", "0");
			formdata.append("store_id", jQuery('#store_name').val());
			formdata.append("self_host", (jQuery('#zt-self-host').val() == jQuery('#zt-host-name').val()) ? '1' : '0');
			formdata.append("host_id", jQuery('#zt-host-name').val());
			formdata.append("test_event", "0");
			formdata.append("product_id", get_product_id());
			formdata.append("duration", jQuery('#event_duration').val());
			formdata.append("cover_image", jQuery('#zt-cover-img')[0].files[0]);
			formdata.append("banner_image_file", "true");
			formdata.append("cover_image_file", "true");
			let offer = jQuery('#zt-offer').val()
			formdata.append('by_offer', offer);
			if (offer == 'true') {
				formdata.append('offer_id', jQuery('#zt-offer-id').val());
			}
			let secret_key = jQuery('#zt-secret-key').val();
			let list_url = jQuery('#event_list_url').val();
			let myHeaders = new Headers();
			myHeaders.append("secret-key", secret_key);
			myHeaders.append("api-type", "1");

			var requestOptions = {
				method: 'POST',
				headers: myHeaders,
				body: formdata,
				redirect: 'follow'
			};

			fetch(`${ztcbl_qv.ztcbl_api_url}create/event`, requestOptions)
				.then(response => response.json()) // Parse the response as JSON
				.then((result) => {
					const message = result.message;
					if (message == 'Event created successfully') {
						jQuery('#create_response').html(message);
						window.location.href = list_url;
					} else {
						jQuery('#create_response').html(message);
					}

				})
				.catch(error => console.log('error', error));
		});

		jQuery('#update_event_form').on('submit', function (e) {
			e.preventDefault();
			let startDate = new Date(jQuery('#event_date').val()).toLocaleDateString('en-GB');
			let startTime = new Date('2000-01-01T' + jQuery('#event_time').val() + ':00').toLocaleString('en-US', { hour: 'numeric', minute: 'numeric', hour12: true });
			let showStartDate = startDate + ' ' + startTime;
			var formdata = new FormData();
			formdata.append("title", jQuery('#store_title').val());
			formdata.append("description", jQuery('#store_desc').val());
			formdata.append("start_time", showStartDate);
			if (jQuery('#zt-banner-img')[0].files[0]) {
				formdata.append('banner_image', jQuery('#zt-banner-img')[0].files[0]);
			} else {
				formdata.append('banner_image', jQuery('#zt-banner').val());
			}
			formdata.append("show_public", "1");
			formdata.append("anonymous_chat", jQuery('.zt-chat-checkbox').prop('checked') ? '1' : '0');
			formdata.append("product_clips", jQuery('.zt-hightlight-checkbox').prop('checked') ? '1' : '0');
			formdata.append("system_message", "1");
			formdata.append("self_host", (jQuery('#zt-self-host').val() == jQuery('#zt-host-name').val()) ? '1' : '0');
			formdata.append("host_id", jQuery('#zt-host-name').val());
			formdata.append("product_id", get_product_id());
			formdata.append("store_type_id", "1");
			formdata.append("duration", jQuery('#event_duration').val());
			formdata.append("test_event", "0");
			if (jQuery('#zt-cover-img')[0].files[0]) {
				formdata.append('cover_image', jQuery('#zt-cover-img')[0].files[0]);
			} else {
				formdata.append('cover_image', jQuery('#zt-cover').val());
			}
			formdata.append("store_id", jQuery('#store_name').val());

			let secret_key = jQuery('#zt-secret-key').val();
			let event_key = jQuery('#zt-event-key').val();
			let list_url = jQuery('#event_list_url').val();
			let myHeaders = new Headers();
			myHeaders.append("secret-key", secret_key);
			myHeaders.append("api-type", "1");

			var requestOptions = {
				method: 'POST',
				headers: myHeaders,
				body: formdata,
				redirect: 'follow'
			};

			fetch(`${ztcbl_qv.ztcbl_api_url}update/event/${event_key}`, requestOptions)
				.then(response => response.json()) // Parse the response as JSON
				.then((result) => {
					const message = result.message;
					if (message == 'event updated successfully') {
						jQuery('#create_response').html(message);
						window.location.href = list_url;
					} else {
						jQuery('#create_response').html(message);
					}

				})
				.catch(error => console.log('error', error));
		});

		document.addEventListener('click', function (event) {
			const dropdown = document.querySelector('.zt-product-dropdown');

			// Check if the clicked element is not part of the dropdown
			if (dropdown && !dropdown.contains(event.target)) {
				// Close the dropdown here
				dropdown.style.display = 'none';
			}
		});

		jQuery(document).on('click', '.zt_cart_button', (function (e) {
			e.preventDefault();
			var $button = jQuery(this); // Store a reference to the button element
			let product_id = jQuery(this).attr('data-product_id');
			let event_id = jQuery(this).attr('data-redirect-id');
			let event_key = jQuery(this).attr('data-redirect-key');
			let watcherId = localStorage.getItem('watcher_unique_id');
			const ajaxurl = jQuery('.high_light_product').attr('data-action-url');
			jQuery(this).addClass('loading');

			jQuery.ajax({
				url: ajaxurl,
				data: {
					action: 'ztcbl_add_to_cart',
					product_id: product_id,
					event_key: event_key,
					event_id: event_id,
					watcher_id: watcherId,
					ajax_nonce: ztcbl_qv.nonce,
				},
				type: 'POST',
				success: function (response) {
					$button.removeClass('loading');
					jQuery('#cart-popup-text').html(response);
					jQuery('#cart-popup').css('display', 'block');
					setTimeout(() => {
						jQuery('#cart-popup').css('display', 'none');
					}, 3000);
					showCartValue();
				}
			});
		}));

	});

function showCartValue() {
	var ajaxurl = jQuery(".high_light_product").attr("data-action-url");
	jQuery.ajax({
		url: ajaxurl,
		type: 'POST',
		data: {
			action: 'get_cart_contents_count'
		},
		success: function (response) {
			jQuery(".cart_item_value").html(response);
		},
		error: function (xhr, status, error) {
			console.log(error);
		},
		complete: function () {
			// Schedule the next update after a delay
			//setTimeout(showCartValue, 3000); // Update every 3 second (adjust as needed)
		}
	});

	jQuery(document).on('submit', '#feedback-form', async function (e) {
		e.preventDefault();
		jQuery('#ztcbl_sent_button').addClass('loading');
		let formdata = new FormData();
		const secret_key = jQuery('.high_light_product').attr('random-key');
		const event_id = jQuery('#high_light_product_card').attr('event_id');
		formdata.append("name", jQuery('#ztcbl-user-name').val());
		formdata.append("email", jQuery('#ztcbl-user-email').val());
		formdata.append("event_id", event_id);
		formdata.append("feedback", jQuery('#ztcbl-user-feedback').val());
		const apiUrl = ztcbl_qv.ztcbl_api_url;
		const header = {
			'secret-key': jQuery('.high_light_product').attr('random-key'),
			'api-type': 1
		};
		const requestOptions = {
			method: 'POST',
			headers: header,
			body: formdata,
			redirect: 'follow'
		};
		const response = await fetch(`${apiUrl}event/feedback`, requestOptions);
		const data = await response.json();
		jQuery('#ztcbl_sent_button').removeClass('loading');
		jQuery('#feedback-message').css('display','block');
		jQuery('#feedback-message').text(data?.message);
		setTimeout(() => {
			jQuery('#feedback-message').text('');
			const redirectUrl = `${ztcbl_qv.ztcbl_site_url}/events-list`;
			window.location.href = redirectUrl;
		}, 1000);
	});
}
