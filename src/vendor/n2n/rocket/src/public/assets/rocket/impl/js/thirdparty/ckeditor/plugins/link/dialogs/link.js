/**
 * @license Copyright (c) 2003-2017, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

'use strict';
( function() {
	CKEDITOR.dialog.add( 'link', function( editor ) {
		var plugin = CKEDITOR.plugins.link,
			initialLinkText;
		
		//////////////////////
		// CUSTOM IMPLEMENTATION
		//////////////////////
		var linkConfigurations = [],
				linkConfigurationData = editor.element.data("link-configurations");
		if (null !== linkConfigurationData) {
			if (null != jQuery) {
				linkConfigurations = jQuery.parseJSON(linkConfigurationData);
			} else if (null != JSON) {
				linkConfigurations = JSON.parse(linkConfigurationData);
			}
		}
		
		// Loads the parameters in a selected link to the link dialog fields.
		var javascriptProtocolRegex = /^javascript:/,
			emailRegex = /^mailto:([^?]+)(?:\?(.+))?$/,
			emailSubjectRegex = /subject=([^;?:@&=$,\/]*)/i,
			emailBodyRegex = /body=([^;?:@&=$,\/]*)/i,
			anchorRegex = /^#(.*)$/,
			urlRegex = /^((?:http|https|ftp|news):\/\/)?(.*)$/,
			selectableTargets = /^(_(?:self|top|parent|blank))$/,
			encodedEmailLinkRegex = /^javascript:void\(location\.href='mailto:'\+String\.fromCharCode\(([^)]+)\)(?:\+'(.*)')?\)$/,
			functionCallProtectedEmailLinkRegex = /^javascript:([^(]+)\(([^)]+)\)$/,
			popupRegex = /\s*window.open\(\s*this\.href\s*,\s*(?:'([^']*)'|null)\s*,\s*'([^']*)'\s*\)\s*;\s*return\s*false;*\s*/,
			popupFeaturesRegex = /(?:^|,)([^=]+)=(\d+|yes|no)/gi;
		
		var advAttrNames = {
				id: 'advId',
				dir: 'advLangDir',
				accessKey: 'advAccessKey',
				// 'data-cke-saved-name': 'advName',
				name: 'advName',
				lang: 'advLangCode',
				tabindex: 'advTabIndex',
				title: 'advTitle',
				type: 'advContentType',
				'class': 'advCSSClasses',
				charset: 'advCharset',
				style: 'advStyles',
				rel: 'advRel'
			};
		/**
		 * Parses attributes of the link element and returns an object representing
		 * the current state (data) of the link. This data format is a plain object accepted
		 * e.g. by the Link dialog window and {@link #getLinkAttributes}.
		 *
		 * **Note:** Data model format produced by the parser must be compatible with the Link
		 * plugin dialog because it is passed directly to {@link CKEDITOR.dialog#setupContent}.
		 *
		 * @since 4.4
		 * @param {CKEDITOR.editor} editor
		 * @param {CKEDITOR.dom.element} element
		 * @returns {Object} An object of link data.
		 */
		plugin.parseLinkAttributes = function( editor, element ) {
			var href = ( element && ( element.data( 'cke-saved-href' ) || element.getAttribute( 'href' ) ) ) || '',
				compiledProtectionFunction = editor.plugins.link.compiledProtectionFunction,
				emailProtection = editor.config.emailProtection,
				javascriptMatch, emailMatch, anchorMatch, urlMatch,
				retval = {};

			if ( ( javascriptMatch = href.match( javascriptProtocolRegex ) ) ) {
				if ( emailProtection == 'encode' ) {
					href = href.replace( encodedEmailLinkRegex, function( match, protectedAddress, rest ) {
						// Without it 'undefined' is appended to e-mails without subject and body (http://dev.ckeditor.com/ticket/9192).
						rest = rest || '';

						return 'mailto:' +
							String.fromCharCode.apply( String, protectedAddress.split( ',' ) ) +
							unescapeSingleQuote( rest );
					} );
				}
				// Protected email link as function call.
				else if ( emailProtection ) {
					href.replace( functionCallProtectedEmailLinkRegex, function( match, funcName, funcArgs ) {
						if ( funcName == compiledProtectionFunction.name ) {
							retval.type = 'email';
							var email = retval.email = {};

							var paramRegex = /[^,\s]+/g,
								paramQuoteRegex = /(^')|('$)/g,
								paramsMatch = funcArgs.match( paramRegex ),
								paramsMatchLength = paramsMatch.length,
								paramName, paramVal;

							for ( var i = 0; i < paramsMatchLength; i++ ) {
								paramVal = decodeURIComponent( unescapeSingleQuote( paramsMatch[ i ].replace( paramQuoteRegex, '' ) ) );
								paramName = compiledProtectionFunction.params[ i ].toLowerCase();
								email[ paramName ] = paramVal;
							}
							email.address = [ email.name, email.domain ].join( '@' );
						}
					} );
				}
			}
			
			if ( !retval.type ) {
				//custom
				for (var name in linkConfigurations) {
					for (var i in linkConfigurations[name]['items']) {
						if (linkConfigurations[name]['items'][i][1] === href) {
							retval.type = name;
							retval[name + "Url"] = href;
							break;
						}
					}
					
					if (retval.type) break;
				}
				
				if ( !retval.type ) { 
					if ( ( anchorMatch = href.match( anchorRegex ) ) ) {
						retval.type = 'anchor';
						retval.anchor = {};
						retval.anchor.name = retval.anchor.id = anchorMatch[ 1 ];
					}
					// Protected email link as encoded string.
					else if ( ( emailMatch = href.match( emailRegex ) ) ) {
						var subjectMatch = href.match( emailSubjectRegex ),
							bodyMatch = href.match( emailBodyRegex );
	
						retval.type = 'email';
						var email = ( retval.email = {} );
						email.address = emailMatch[ 1 ];
						subjectMatch && ( email.subject = decodeURIComponent( subjectMatch[ 1 ] ) );
						bodyMatch && ( email.body = decodeURIComponent( bodyMatch[ 1 ] ) );
					}
					// urlRegex matches empty strings, so need to check for href as well.
					else if ( href && ( urlMatch = href.match( urlRegex ) ) ) {
						retval.type = 'url';
						retval.url = {};
						retval.url.protocol = urlMatch[ 1 ];
						retval.url.url = urlMatch[ 2 ];
					}
				}
			}

			// Load target and popup settings.
			if ( element ) {
				var target = element.getAttribute( 'target' );

				// IE BUG: target attribute is an empty string instead of null in IE if it's not set.
				if ( !target ) {
					var onclick = element.data( 'cke-pa-onclick' ) || element.getAttribute( 'onclick' ),
						onclickMatch = onclick && onclick.match( popupRegex );

					if ( onclickMatch ) {
						retval.target = {
							type: 'popup',
							name: onclickMatch[ 1 ]
						};

						var featureMatch;
						while ( ( featureMatch = popupFeaturesRegex.exec( onclickMatch[ 2 ] ) ) ) {
							// Some values should remain numbers (http://dev.ckeditor.com/ticket/7300)
							if ( ( featureMatch[ 2 ] == 'yes' || featureMatch[ 2 ] == '1' ) && !( featureMatch[ 1 ] in { height: 1, width: 1, top: 1, left: 1 } ) )
								retval.target[ featureMatch[ 1 ] ] = true;
							else if ( isFinite( featureMatch[ 2 ] ) )
								retval.target[ featureMatch[ 1 ] ] = featureMatch[ 2 ];
						}
					}
				} else {
					retval.target = {
						type: target.match( selectableTargets ) ? target : 'frame',
						name: target
					};
				}

				var download = element.getAttribute( 'download' );
				if ( download !== null ) {
					retval.download = true;
				}

				var advanced = {};

				for ( var a in advAttrNames ) {
					var val = element.getAttribute( a );

					if ( val )
						advanced[ advAttrNames[ a ] ] = val;
				}

				var advName = element.data( 'cke-saved-name' ) || advanced.advName;

				if ( advName )
					advanced.advName = advName;

				if ( !CKEDITOR.tools.isEmpty( advanced ) )
					retval.advanced = advanced;
			}

			return retval;
		}
		
		/**
		 * Converts link data produced by {@link #parseLinkAttributes} into an object which consists
		 * of attributes to be set (with their values) and an array of attributes to be removed.
		 * This method can be used to compose or to update any link element with the given data.
		 *
		 * @since 4.4
		 * @param {CKEDITOR.editor} editor
		 * @param {Object} data Data in {@link #parseLinkAttributes} format.
		 * @returns {Object} An object consisting of two keys, i.e.:
		 *
		 *		{
		 *			// Attributes to be set.
		 *			set: {
		 *				href: 'http://foo.bar',
		 *				target: 'bang'
		 *			},
		 *			// Attributes to be removed.
		 *			removed: [
		 *				'id', 'style'
		 *			]
		 *		}
		 *
		 */
		plugin.getLinkAttributes = function( editor, data ) {
			var emailProtection = editor.config.emailProtection || '',
				set = {};

			// Compose the URL.
			switch ( data.type ) {
				case 'url':
					var protocol = ( data.url && data.url.protocol !== undefined ) ? data.url.protocol : 'http://',
						url = ( data.url && CKEDITOR.tools.trim( data.url.url ) ) || '';

					set[ 'data-cke-saved-href' ] = ( url.indexOf( '/' ) === 0 ) ? url : protocol + url;

					break;
				case 'anchor':
					var name = ( data.anchor && data.anchor.name ),
						id = ( data.anchor && data.anchor.id );

					set[ 'data-cke-saved-href' ] = '#' + ( name || id || '' );

					break;
				case 'email':
					var email = data.email,
						address = email.address,
						linkHref;

					switch ( emailProtection ) {
						case '':
						case 'encode':
							var subject = encodeURIComponent( email.subject || '' ),
								body = encodeURIComponent( email.body || '' ),
								argList = [];

							// Build the e-mail parameters first.
							subject && argList.push( 'subject=' + subject );
							body && argList.push( 'body=' + body );
							argList = argList.length ? '?' + argList.join( '&' ) : '';

							if ( emailProtection == 'encode' ) {
								linkHref = [
									'javascript:void(location.href=\'mailto:\'+', // jshint ignore:line
									protectEmailAddressAsEncodedString( address )
								];
								// parameters are optional.
								argList && linkHref.push( '+\'', escapeSingleQuote( argList ), '\'' );

								linkHref.push( ')' );
							} else {
								linkHref = [ 'mailto:', address, argList ];
							}

							break;
						default:
							// Separating name and domain.
							var nameAndDomain = address.split( '@', 2 );
							email.name = nameAndDomain[ 0 ];
							email.domain = nameAndDomain[ 1 ];

							linkHref = [ 'javascript:', protectEmailLinkAsFunction( editor, email ) ]; // jshint ignore:line
					}

					set[ 'data-cke-saved-href' ] = linkHref.join( '' );
					break;
					
				default:
					//////////////////
					// custom
					/////////////////
					if (null != linkConfigurations[data.type]) {
						if (linkConfigurations[data.type]['open-in-new-window']) {
							set[ 'target' ] = '_blank';
						} else {
							data.target.type = 'notSet';
						}
						set[ 'data-cke-saved-href' ] = data[data.type + "Url"];
						url = data[data.type + "Url"];
					}
					break;
					///////
					// end custom
					///////
					
			}

			// Popups and target.
			if ( data.target ) {
				if ( data.target.type == 'popup' ) {
					var onclickList = [
							'window.open(this.href, \'', data.target.name || '', '\', \''
						],
						featureList = [
							'resizable', 'status', 'location', 'toolbar', 'menubar', 'fullscreen', 'scrollbars', 'dependent'
						],
						featureLength = featureList.length,
						addFeature = function( featureName ) {
							if ( data.target[ featureName ] )
								featureList.push( featureName + '=' + data.target[ featureName ] );
						};

					for ( var i = 0; i < featureLength; i++ )
						featureList[ i ] = featureList[ i ] + ( data.target[ featureList[ i ] ] ? '=yes' : '=no' );

					addFeature( 'width' );
					addFeature( 'left' );
					addFeature( 'height' );
					addFeature( 'top' );

					onclickList.push( featureList.join( ',' ), '\'); return false;' );
					set[ 'data-cke-pa-onclick' ] = onclickList.join( '' );
				}
				else if ( data.target.type != 'notSet' && data.target.name ) {
					set.target = data.target.name;
				}
			}

			// Force download attribute.
			if ( data.download ) {
				set.download = '';
			}

			// Advanced attributes.
			if ( data.advanced ) {
				for ( var a in advAttrNames ) {
					var val = data.advanced[ advAttrNames[ a ] ];

					if ( val )
						set[ a ] = val;
				}

				if ( set.name )
					set[ 'data-cke-saved-name' ] = set.name;
			}

			// Browser need the "href" fro copy/paste link to work. (http://dev.ckeditor.com/ticket/6641)
			if ( set[ 'data-cke-saved-href' ] )
				set.href = set[ 'data-cke-saved-href' ];

			var removed = {
				target: 1,
				onclick: 1,
				'data-cke-pa-onclick': 1,
				'data-cke-saved-name': 1,
				'download': 1
			};

			if ( data.advanced )
				CKEDITOR.tools.extend( removed, advAttrNames );

			// Remove all attributes which are not currently set.
			for ( var s in set )
				delete removed[ s ];

			return {
				set: set,
				removed: CKEDITOR.tools.objectKeys( removed )
			};
		};
		
		//////////////////////
		// End Custom
		//////////////////////
		

		function createRangeForLink( editor, link ) {
			var range = editor.createRange();

			range.setStartBefore( link );
			range.setEndAfter( link );

			return range;
		}

		function insertLinksIntoSelection( editor, data ) {
			var attributes = plugin.getLinkAttributes( editor, data, linkConfigurations ),
				ranges = editor.getSelection().getRanges(),
				style = new CKEDITOR.style( {
					element: 'a',
					attributes: attributes.set
				} ),
				rangesToSelect = [],
				range,
				text,
				nestedLinks,
				i,
				j;
			
			style.type = CKEDITOR.STYLE_INLINE; // need to override... dunno why.

			for ( i = 0; i < ranges.length; i++ ) {
				range = ranges[ i ];

				// Use link URL as text with a collapsed cursor.
				if ( range.collapsed ) {
					// Short mailto link text view (http://dev.ckeditor.com/ticket/5736).
					text = new CKEDITOR.dom.text( data.linkText || ( data.type == 'email' ?
						data.email.address : attributes.set[ 'data-cke-saved-href' ] ), editor.document );
					range.insertNode( text );
					range.selectNodeContents( text );
				} else if ( initialLinkText !== data.linkText ) {
					text = new CKEDITOR.dom.text( data.linkText, editor.document );

					// Shrink range to preserve block element.
					range.shrink( CKEDITOR.SHRINK_TEXT );

					// Use extractHtmlFromRange to remove markup within the selection. Also this method is a little
					// smarter than range#deleteContents as it plays better e.g. with table cells.
					editor.editable().extractHtmlFromRange( range );

					range.insertNode( text );
				}

				// Editable links nested within current range should be removed, so that the link is applied to whole selection.
				nestedLinks = range._find( 'a' );

				for	( j = 0; j < nestedLinks.length; j++ ) {
					nestedLinks[ j ].remove( true );
				}


				// Apply style.
				style.applyToRange( range, editor );

				rangesToSelect.push( range );
			}

			editor.getSelection().selectRanges( rangesToSelect );
		}

		function editLinksInSelection( editor, selectedElements, data ) {
			var attributes = plugin.getLinkAttributes( editor, data, linkConfigurations ),
				ranges = [],
				element,
				href,
				textView,
				newText,
				i;

			for ( i = 0; i < selectedElements.length; i++ ) {
				// We're only editing an existing link, so just overwrite the attributes.
				element = selectedElements[ i ];
				href = element.data( 'cke-saved-href' );
				textView = element.getHtml();

				element.setAttributes( attributes.set );
				element.removeAttributes( attributes.removed );


				if ( data.linkText && initialLinkText != data.linkText ) {
					// Display text has been changed.
					newText = data.linkText;
				} else if ( href == textView || data.type == 'email' && textView.indexOf( '@' ) != -1 ) {
					// Update text view when user changes protocol (http://dev.ckeditor.com/ticket/4612).
					// Short mailto link text view (http://dev.ckeditor.com/ticket/5736).
					newText = data.type == 'email' ? data.email.address : attributes.set[ 'data-cke-saved-href' ];
				}

				if ( newText ) {
					element.setText( newText );
				}

				ranges.push( createRangeForLink( editor, element ) );
			}

			// We changed the content, so need to select it again.
			editor.getSelection().selectRanges( ranges );
		}

		// Handles the event when the "Target" selection box is changed.
		var targetChanged = function() {
				var dialog = this.getDialog(),
					popupFeatures = dialog.getContentElement( 'target', 'popupFeatures' ),
					targetName = dialog.getContentElement( 'target', 'linkTargetName' ),
					value = this.getValue();

				if ( !popupFeatures || !targetName )
					return;

				popupFeatures = popupFeatures.getElement();
				popupFeatures.hide();
				targetName.setValue( '' );

				switch ( value ) {
					case 'frame':
						targetName.setLabel( editor.lang.link.targetFrameName );
						targetName.getElement().show();
						break;
					case 'popup':
						popupFeatures.show();
						targetName.setLabel( editor.lang.link.targetPopupName );
						targetName.getElement().show();
						break;
					default:
						targetName.setValue( value );
						targetName.getElement().hide();
						break;
				}

			};

		// Handles the event when the "Type" selection box is changed.
		var linkTypeChanged = function() {
				var dialog = this.getDialog(),
					partIds = [ 'urlOptions', 'anchorOptions', 'emailOptions' ],
					typeValue = this.getValue(),
					uploadTab = dialog.definition.getContents( 'upload' ),
					uploadInitiallyHidden = uploadTab && uploadTab.hidden;

				//////////////////
				// custom
				/////////////////
				for (var name in linkConfigurations) {
					if (linkConfigurations[name].length === 0) continue;
					partIds.push(name + "Options");
				}
				/////////////////
				// end custom
				/////////////////
				
				if ( typeValue == 'url' ) {
					if ( editor.config.linkShowTargetTab )
						dialog.showPage( 'target' );
					if ( !uploadInitiallyHidden )
						dialog.showPage( 'upload' );
				} else {
					dialog.hidePage( 'target' );
					if ( !uploadInitiallyHidden )
						dialog.hidePage( 'upload' );
				}

				for ( var i = 0; i < partIds.length; i++ ) {
					var element = dialog.getContentElement( 'info', partIds[ i ] );
					if ( !element )
						continue;

					element = element.getElement().getParent().getParent();
					if ( partIds[ i ] == typeValue + 'Options' )
						element.show();
					else
						element.hide();
				}

				dialog.layout();
			};

		var setupParams = function( page, data ) {
				if ( data[ page ] )
					this.setValue( data[ page ][ this.id ] || '' );
			};

		var setupPopupParams = function( data ) {
				return setupParams.call( this, 'target', data );
			};

		var setupAdvParams = function( data ) {
				return setupParams.call( this, 'advanced', data );
			};

		var commitParams = function( page, data ) {
				if ( !data[ page ] )
					data[ page ] = {};

				data[ page ][ this.id ] = this.getValue() || '';
			};

		var commitPopupParams = function( data ) {
				return commitParams.call( this, 'target', data );
			};

		var commitAdvParams = function( data ) {
				return commitParams.call( this, 'advanced', data );
			};

		var commonLang = editor.lang.common,
			linkLang = editor.lang.link,
			anchors;

		////////////////////
		// custom
		///////////////////
		var linkTypeItems = [
			[ linkLang.toUrl, 'url' ],
			[ linkLang.toAnchor, 'anchor' ],
			[ linkLang.toEmail, 'email' ]
		];
		
		var infoElements = [ {
			type: 'text',
			id: 'linkDisplayText',
			label: linkLang.displayText,
			setup: function() {
				this.enable();

				this.setValue( editor.getSelection().getSelectedText() );

				// Keep inner text so that it can be compared in commit function. By obtaining value from getData()
				// we get value stripped from new line chars which is important when comparing the value later on.
				initialLinkText = this.getValue();
			},
			commit: function( data ) {
				data.linkText = this.isEnabled() ? this.getValue() : '';
			}
		},
		{
			id: 'linkType',
			type: 'select',
			label: linkLang.type,
			'default': 'url',
			items: linkTypeItems,
			onChange: linkTypeChanged,
			setup: function( data ) {
				this.setValue( data.type || 'url' );
			},
			commit: function( data ) {
				data.type = this.getValue();
			}
		},
		{
			type: 'vbox',
			id: 'urlOptions',
			children: [ {
				type: 'hbox',
				widths: [ '25%', '75%' ],
				children: [ {
					id: 'protocol',
					type: 'select',
					label: commonLang.protocol,
					'default': 'http://',
					items: [
						// Force 'ltr' for protocol names in BIDI. (http://dev.ckeditor.com/ticket/5433)
						[ 'http://\u200E', 'http://' ],
						[ 'https://\u200E', 'https://' ],
						[ 'ftp://\u200E', 'ftp://' ],
						[ 'news://\u200E', 'news://' ],
						[ linkLang.other, '' ]
					],
					setup: function( data ) {
						if ( data.url )
							this.setValue( data.url.protocol || '' );
					},
					commit: function( data ) {
						if ( !data.url )
							data.url = {};

						data.url.protocol = this.getValue();
					}
				},
				{
					type: 'text',
					id: 'url',
					label: commonLang.url,
					required: true,
					onLoad: function() {
						this.allowOnChange = true;
					},
					onKeyUp: function() {
						this.allowOnChange = false;
						var protocolCmb = this.getDialog().getContentElement( 'info', 'protocol' ),
							url = this.getValue(),
							urlOnChangeProtocol = /^(http|https|ftp|news):\/\/(?=.)/i,
							urlOnChangeTestOther = /^((javascript:)|[#\/\.\?])/i;

						var protocol = urlOnChangeProtocol.exec( url );
						if ( protocol ) {
							this.setValue( url.substr( protocol[ 0 ].length ) );
							protocolCmb.setValue( protocol[ 0 ].toLowerCase() );
						} else if ( urlOnChangeTestOther.test( url ) ) {
							protocolCmb.setValue( '' );
						}

						this.allowOnChange = true;
					},
					onChange: function() {
						if ( this.allowOnChange ) // Dont't call on dialog load.
						this.onKeyUp();
					},
					validate: function() {
						var dialog = this.getDialog();

						if ( dialog.getContentElement( 'info', 'linkType' ) && dialog.getValueOf( 'info', 'linkType' ) != 'url' )
							return true;

						if ( !editor.config.linkJavaScriptLinksAllowed && ( /javascript\:/ ).test( this.getValue() ) ) {
							alert( commonLang.invalidValue ); // jshint ignore:line
							return false;
						}

						if ( this.getDialog().fakeObj ) // Edit Anchor.
						return true;

						var func = CKEDITOR.dialog.validate.notEmpty( linkLang.noUrl );
						return func.apply( this );
					},
					setup: function( data ) {
						this.allowOnChange = false;
						if ( data.url )
							this.setValue( data.url.url );
						this.allowOnChange = true;

					},
					commit: function( data ) {
						// IE will not trigger the onChange event if the mouse has been used
						// to carry all the operations http://dev.ckeditor.com/ticket/4724
						this.onChange();

						if ( !data.url )
							data.url = {};

						data.url.url = this.getValue();
						this.allowOnChange = false;
					}
				} ],
				setup: function() {
					if ( !this.getDialog().getContentElement( 'info', 'linkType' ) )
						this.getElement().show();
				}
			},
			{
				type: 'button',
				id: 'browse',
				hidden: 'true',
				filebrowser: 'info:url',
				label: commonLang.browseServer
			} ]
		},
		{
			type: 'vbox',
			id: 'anchorOptions',
			width: 260,
			align: 'center',
			padding: 0,
			children: [ {
				type: 'fieldset',
				id: 'selectAnchorText',
				label: linkLang.selectAnchor,
				setup: function() {
					anchors = plugin.getEditorAnchors( editor );

					this.getElement()[ anchors && anchors.length ? 'show' : 'hide' ]();
				},
				children: [ {
					type: 'hbox',
					id: 'selectAnchor',
					children: [ {
						type: 'select',
						id: 'anchorName',
						'default': '',
						label: linkLang.anchorName,
						style: 'width: 100%;',
						items: [
							[ '' ]
						],
						setup: function( data ) {
							this.clear();
							this.add( '' );

							if ( anchors ) {
								for ( var i = 0; i < anchors.length; i++ ) {
									if ( anchors[ i ].name )
										this.add( anchors[ i ].name );
								}
							}

							if ( data.anchor )
								this.setValue( data.anchor.name );

							var linkType = this.getDialog().getContentElement( 'info', 'linkType' );
							if ( linkType && linkType.getValue() == 'email' )
								this.focus();
						},
						commit: function( data ) {
							if ( !data.anchor )
								data.anchor = {};

							data.anchor.name = this.getValue();
						}
					},
					{
						type: 'select',
						id: 'anchorId',
						'default': '',
						label: linkLang.anchorId,
						style: 'width: 100%;',
						items: [
							[ '' ]
						],
						setup: function( data ) {
							this.clear();
							this.add( '' );

							if ( anchors ) {
								for ( var i = 0; i < anchors.length; i++ ) {
									if ( anchors[ i ].id )
										this.add( anchors[ i ].id );
								}
							}

							if ( data.anchor )
								this.setValue( data.anchor.id );
						},
						commit: function( data ) {
							if ( !data.anchor )
								data.anchor = {};

							data.anchor.id = this.getValue();
						}
					} ],
					setup: function() {
						this.getElement()[ anchors && anchors.length ? 'show' : 'hide' ]();
					}
				} ]
			},
			{
				type: 'html',
				id: 'noAnchors',
				style: 'text-align: center;',
				html: '<div role="note" tabIndex="-1">' + CKEDITOR.tools.htmlEncode( linkLang.noAnchors ) + '</div>',
				// Focus the first element defined in above html.
				focus: true,
				setup: function() {
					this.getElement()[ anchors && anchors.length ? 'hide' : 'show' ]();
				}
			} ],
			setup: function() {
				if ( !this.getDialog().getContentElement( 'info', 'linkType' ) )
					this.getElement().hide();
			}
		},
		{
			type: 'vbox',
			id: 'emailOptions',
			padding: 1,
			children: [ {
				type: 'text',
				id: 'emailAddress',
				label: linkLang.emailAddress,
				required: true,
				validate: function() {
					var dialog = this.getDialog();

					if ( !dialog.getContentElement( 'info', 'linkType' ) || dialog.getValueOf( 'info', 'linkType' ) != 'email' )
						return true;

					var func = CKEDITOR.dialog.validate.notEmpty( linkLang.noEmail );
					return func.apply( this );
				},
				setup: function( data ) {
					if ( data.email )
						this.setValue( data.email.address );

					var linkType = this.getDialog().getContentElement( 'info', 'linkType' );
					if ( linkType && linkType.getValue() == 'email' )
						this.select();
				},
				commit: function( data ) {
					if ( !data.email )
						data.email = {};

					data.email.address = this.getValue();
				}
			},
			{
				type: 'text',
				id: 'emailSubject',
				label: linkLang.emailSubject,
				setup: function( data ) {
					if ( data.email )
						this.setValue( data.email.subject );
				},
				commit: function( data ) {
					if ( !data.email )
						data.email = {};

					data.email.subject = this.getValue();
				}
			},
			{
				type: 'textarea',
				id: 'emailBody',
				label: linkLang.emailBody,
				rows: 3,
				'default': '',
				setup: function( data ) {
					if ( data.email )
						this.setValue( data.email.body );
				},
				commit: function( data ) {
					if ( !data.email )
						data.email = {};

					data.email.body = this.getValue();
				}
			} ],
			setup: function() {
				if ( !this.getDialog().getContentElement( 'info', 'linkType' ) )
					this.getElement().hide();
			}
		} ];
		
		for (var name in linkConfigurations) {
			if (linkConfigurations[name].length === 0) continue;
			(function() {
				var tmpName = name;
				linkTypeItems.push([tmpName, tmpName]);
				infoElements.push(
					{
						id: tmpName + 'Options',
						type: 'select',
						label: tmpName,
						'default': linkConfigurations[tmpName]['items'][0],
						items: 
							linkConfigurations[tmpName]['items']
						,
						setup: function( data ) {
							if ( data[tmpName + "Url"] )
								this.setValue( data[tmpName + "Url"] );
						},
						commit: function( data ) {
							data[tmpName + "Url"] = this.getValue();
						}
					}
				);
			})();
		};
		
		return {
			title: linkLang.title,
			minWidth: ( CKEDITOR.skinName || editor.config.skin ) == 'moono-lisa' ? 450 : 350,
			minHeight: 240,
			contents: [ {
				id: 'info',
				label: linkLang.info,
				title: linkLang.info,
				elements: infoElements 
			},
			{
				id: 'target',
				requiredContent: 'a[target]', // This is not fully correct, because some target option requires JS.
				label: linkLang.target,
				title: linkLang.target,
				elements: [ {
					type: 'hbox',
					widths: [ '50%', '50%' ],
					children: [ {
						type: 'select',
						id: 'linkTargetType',
						label: commonLang.target,
						'default': 'notSet',
						style: 'width : 100%;',
						'items': [
							[ commonLang.notSet, 'notSet' ],
							[ linkLang.targetFrame, 'frame' ],
							[ linkLang.targetPopup, 'popup' ],
							[ commonLang.targetNew, '_blank' ],
							[ commonLang.targetTop, '_top' ],
							[ commonLang.targetSelf, '_self' ],
							[ commonLang.targetParent, '_parent' ]
						],
						onChange: targetChanged,
						setup: function( data ) {
							if ( data.target )
								this.setValue( data.target.type || 'notSet' );
							targetChanged.call( this );
						},
						commit: function( data ) {
							if ( !data.target )
								data.target = {};

							data.target.type = this.getValue();
						}
					},
					{
						type: 'text',
						id: 'linkTargetName',
						label: linkLang.targetFrameName,
						'default': '',
						setup: function( data ) {
							if ( data.target )
								this.setValue( data.target.name );
						},
						commit: function( data ) {
							if ( !data.target )
								data.target = {};

							data.target.name = this.getValue().replace( /([^\x00-\x7F]|\s)/gi, '' );
						}
					} ]
				},
				{
					type: 'vbox',
					width: '100%',
					align: 'center',
					padding: 2,
					id: 'popupFeatures',
					children: [ {
						type: 'fieldset',
						label: linkLang.popupFeatures,
						children: [ {
							type: 'hbox',
							children: [ {
								type: 'checkbox',
								id: 'resizable',
								label: linkLang.popupResizable,
								setup: setupPopupParams,
								commit: commitPopupParams
							},
							{
								type: 'checkbox',
								id: 'status',
								label: linkLang.popupStatusBar,
								setup: setupPopupParams,
								commit: commitPopupParams

							} ]
						},
						{
							type: 'hbox',
							children: [ {
								type: 'checkbox',
								id: 'location',
								label: linkLang.popupLocationBar,
								setup: setupPopupParams,
								commit: commitPopupParams

							},
							{
								type: 'checkbox',
								id: 'toolbar',
								label: linkLang.popupToolbar,
								setup: setupPopupParams,
								commit: commitPopupParams

							} ]
						},
						{
							type: 'hbox',
							children: [ {
								type: 'checkbox',
								id: 'menubar',
								label: linkLang.popupMenuBar,
								setup: setupPopupParams,
								commit: commitPopupParams

							},
							{
								type: 'checkbox',
								id: 'fullscreen',
								label: linkLang.popupFullScreen,
								setup: setupPopupParams,
								commit: commitPopupParams

							} ]
						},
						{
							type: 'hbox',
							children: [ {
								type: 'checkbox',
								id: 'scrollbars',
								label: linkLang.popupScrollBars,
								setup: setupPopupParams,
								commit: commitPopupParams

							},
							{
								type: 'checkbox',
								id: 'dependent',
								label: linkLang.popupDependent,
								setup: setupPopupParams,
								commit: commitPopupParams

							} ]
						},
						{
							type: 'hbox',
							children: [ {
								type: 'text',
								widths: [ '50%', '50%' ],
								labelLayout: 'horizontal',
								label: commonLang.width,
								id: 'width',
								setup: setupPopupParams,
								commit: commitPopupParams

							},
							{
								type: 'text',
								labelLayout: 'horizontal',
								widths: [ '50%', '50%' ],
								label: linkLang.popupLeft,
								id: 'left',
								setup: setupPopupParams,
								commit: commitPopupParams

							} ]
						},
						{
							type: 'hbox',
							children: [ {
								type: 'text',
								labelLayout: 'horizontal',
								widths: [ '50%', '50%' ],
								label: commonLang.height,
								id: 'height',
								setup: setupPopupParams,
								commit: commitPopupParams

							},
							{
								type: 'text',
								labelLayout: 'horizontal',
								label: linkLang.popupTop,
								widths: [ '50%', '50%' ],
								id: 'top',
								setup: setupPopupParams,
								commit: commitPopupParams

							} ]
						} ]
					} ]
				} ]
			},
			{
				id: 'upload',
				label: linkLang.upload,
				title: linkLang.upload,
				hidden: true,
				filebrowser: 'uploadButton',
				elements: [ {
					type: 'file',
					id: 'upload',
					label: commonLang.upload,
					style: 'height:40px',
					size: 29
				},
				{
					type: 'fileButton',
					id: 'uploadButton',
					label: commonLang.uploadSubmit,
					filebrowser: 'info:url',
					'for': [ 'upload', 'upload' ]
				} ]
			},
			{
				id: 'advanced',
				label: linkLang.advanced,
				title: linkLang.advanced,
				elements: [ {
					type: 'vbox',
					padding: 1,
					children: [ {
						type: 'hbox',
						widths: [ '45%', '35%', '20%' ],
						children: [ {
							type: 'text',
							id: 'advId',
							requiredContent: 'a[id]',
							label: linkLang.id,
							setup: setupAdvParams,
							commit: commitAdvParams
						},
						{
							type: 'select',
							id: 'advLangDir',
							requiredContent: 'a[dir]',
							label: linkLang.langDir,
							'default': '',
							style: 'width:110px',
							items: [
								[ commonLang.notSet, '' ],
								[ linkLang.langDirLTR, 'ltr' ],
								[ linkLang.langDirRTL, 'rtl' ]
							],
							setup: setupAdvParams,
							commit: commitAdvParams
						},
						{
							type: 'text',
							id: 'advAccessKey',
							requiredContent: 'a[accesskey]',
							width: '80px',
							label: linkLang.acccessKey,
							maxLength: 1,
							setup: setupAdvParams,
							commit: commitAdvParams

						} ]
					},
					{
						type: 'hbox',
						widths: [ '45%', '35%', '20%' ],
						children: [ {
							type: 'text',
							label: linkLang.name,
							id: 'advName',
							requiredContent: 'a[name]',
							setup: setupAdvParams,
							commit: commitAdvParams

						},
						{
							type: 'text',
							label: linkLang.langCode,
							id: 'advLangCode',
							requiredContent: 'a[lang]',
							width: '110px',
							'default': '',
							setup: setupAdvParams,
							commit: commitAdvParams

						},
						{
							type: 'text',
							label: linkLang.tabIndex,
							id: 'advTabIndex',
							requiredContent: 'a[tabindex]',
							width: '80px',
							maxLength: 5,
							setup: setupAdvParams,
							commit: commitAdvParams

						} ]
					} ]
				},
				{
					type: 'vbox',
					padding: 1,
					children: [ {
						type: 'hbox',
						widths: [ '45%', '55%' ],
						children: [ {
							type: 'text',
							label: linkLang.advisoryTitle,
							requiredContent: 'a[title]',
							'default': '',
							id: 'advTitle',
							setup: setupAdvParams,
							commit: commitAdvParams

						},
						{
							type: 'text',
							label: linkLang.advisoryContentType,
							requiredContent: 'a[type]',
							'default': '',
							id: 'advContentType',
							setup: setupAdvParams,
							commit: commitAdvParams

						} ]
					},
					{
						type: 'hbox',
						widths: [ '45%', '55%' ],
						children: [ {
							type: 'text',
							label: linkLang.cssClasses,
							requiredContent: 'a(cke-xyz)', // Random text like 'xyz' will check if all are allowed.
							'default': '',
							id: 'advCSSClasses',
							setup: setupAdvParams,
							commit: commitAdvParams

						},
						{
							type: 'text',
							label: linkLang.charset,
							requiredContent: 'a[charset]',
							'default': '',
							id: 'advCharset',
							setup: setupAdvParams,
							commit: commitAdvParams

						} ]
					},
					{
						type: 'hbox',
						widths: [ '45%', '55%' ],
						children: [ {
							type: 'text',
							label: linkLang.rel,
							requiredContent: 'a[rel]',
							'default': '',
							id: 'advRel',
							setup: setupAdvParams,
							commit: commitAdvParams
						},
						{
							type: 'text',
							label: linkLang.styles,
							requiredContent: 'a{cke-xyz}', // Random text like 'xyz' will check if all are allowed.
							'default': '',
							id: 'advStyles',
							validate: CKEDITOR.dialog.validate.inlineStyle( editor.lang.common.invalidInlineStyle ),
							setup: setupAdvParams,
							commit: commitAdvParams
						} ]
					},
					{
						type: 'hbox',
						widths: [ '45%', '55%' ],
						children: [ {
							type: 'checkbox',
							id: 'download',
							requiredContent: 'a[download]',
							label: linkLang.download,
							setup: function( data ) {
								if ( data.download !== undefined )
									this.setValue( 'checked', 'checked' );
							},
							commit: function( data ) {
								if ( this.getValue() ) {
									data.download = this.getValue();
								}
							}
						} ]
					} ]
				} ]
			} ],
			onShow: function() {
				var editor = this.getParentEditor(),
					selection = editor.getSelection(),
					displayTextField = this.getContentElement( 'info', 'linkDisplayText' ).getElement().getParent().getParent(),
					elements = plugin.getSelectedLink( editor, true ),
					firstLink = elements[ 0 ] || null;

				// Fill in all the relevant fields if there's already one link selected.
				if ( firstLink && firstLink.hasAttribute( 'href' ) ) {
					// Don't change selection if some element is already selected.
					// For example - don't destroy fake selection.
					if ( !selection.getSelectedElement() && !selection.isInTable() ) {
						selection.selectElement( firstLink );
					}
				}

				var data = plugin.parseLinkAttributes( editor, firstLink);

				// Here we'll decide whether or not we want to show Display Text field.
				if ( elements.length <= 1 && plugin.showDisplayTextForElement( firstLink, editor ) ) {
					displayTextField.show();
				} else {
					displayTextField.hide();
				}

				// Record down the selected element in the dialog.
				this._.selectedElements = elements;

				this.setupContent( data );
			},
			onOk: function() {
				var data = {};

				// Collect data from fields.
				this.commitContent( data );

				if ( !this._.selectedElements.length ) {
					insertLinksIntoSelection( editor, data );
				} else {
					editLinksInSelection( editor, this._.selectedElements, data );

					delete this._.selectedElements;
				}
			},
			onLoad: function() {
				if ( !editor.config.linkShowAdvancedTab )
					this.hidePage( 'advanced' ); //Hide Advanded tab.

				if ( !editor.config.linkShowTargetTab )
					this.hidePage( 'target' ); //Hide Target tab.
			},
			// Inital focus on 'url' field if link is of type URL.
			onFocus: function() {
				var linkType = this.getContentElement( 'info', 'linkType' ),
					urlField;

				if ( linkType && linkType.getValue() == 'url' ) {
					urlField = this.getContentElement( 'info', 'url' );
					urlField.select();
				}
			}
		};
	} );
} )();
// jscs:disable maximumLineLength
/**
 * The e-mail address anti-spam protection option. The protection will be
 * applied when creating or modifying e-mail links through the editor interface.
 *
 * Two methods of protection can be chosen:
 *
 * 1. The e-mail parts (name, domain, and any other query string) are
 *     assembled into a function call pattern. Such function must be
 *     provided by the developer in the pages that will use the contents.
 * 2. Only the e-mail address is obfuscated into a special string that
 *     has no meaning for humans or spam bots, but which is properly
 *     rendered and accepted by the browser.
 *
 * Both approaches require JavaScript to be enabled.
 *
 *		// href="mailto:tester@ckeditor.com?subject=subject&body=body"
 *		config.emailProtection = '';
 *
 *		// href="<a href=\"javascript:void(location.href=\'mailto:\'+String.fromCharCode(116,101,115,116,101,114,64,99,107,101,100,105,116,111,114,46,99,111,109)+\'?subject=subject&body=body\')\">e-mail</a>"
 *		config.emailProtection = 'encode';
 *
 *		// href="javascript:mt('tester','ckeditor.com','subject','body')"
 *		config.emailProtection = 'mt(NAME,DOMAIN,SUBJECT,BODY)';
 *
 * @since 3.1
 * @cfg {String} [emailProtection='' (empty string = disabled)]
 * @member CKEDITOR.config
 */
