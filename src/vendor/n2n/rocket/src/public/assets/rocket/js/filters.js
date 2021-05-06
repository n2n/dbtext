/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the n2n module ROCKET.
 *
 * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg...........:	Architect, Lead Developer, Concept
 * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
 * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
 */
jQuery(document).ready(function($) {
	var MultiAdd = function(jqElemA, jqElemContent) {
		this.jqElemA = jqElemA;
		
		this.jqElemContentContainer = $("<div />", {
			"class": "rocket-multi-add-content-container"
		}).css({
			"position": "fixed",
			"zIndex": 1000
		}).hide().insertAfter(jqElemA);
		
		this.jqElemArrow = $("<span />").insertAfter(this.jqElemContentContainer).css({
			"position": "fixed",
			"background": "#818a91",
			"transform": "rotate(45deg)",
			"width": "15px",
			"height": "15px",
			"zIndex": 999
		}).addClass("rocket-multi-add-arrow-left").hide();
		
		this.jqElemContent = jqElemContent.addClass("rocket-multi-add-entries").appendTo(this.jqElemContentContainer);
		
		(function(that) {
			this.jqElemA.click(function(e) {
				e.preventDefault();
				e.stopPropagation();
				if (that.jqElemContentContainer.is(":hidden")) {
					that.showList();
				} else {
					that.hideList();
				}
			});
			
			this.jqElemContentContainer.click(function() {
				that.hideList();
			});
		}).call(this, this);
	};

	MultiAdd.prototype.showList = function() {
		this.jqElemContentContainer.show();
		var left = this.jqElemA.offset().left + this.jqElemA.outerWidth();
		
		this.jqElemArrow.show().css({
			"top": this.jqElemA.offset().top + (this.jqElemA.outerHeight() / 2) 
					- (this.jqElemArrow.outerHeight() / 2),
			"left": left + 2
		});
		  
		left += this.jqElemArrow.outerWidth() / 2;
		
		this.jqElemContentContainer.css({
			"top": this.determineContentTopPos(),
			"left": left
		});
		
		this.applyFieldListMouseLeave();
	};
	
	MultiAdd.prototype.determineContentTopPos = function() {
		return this.jqElemA.offset().top +  this.jqElemA.outerHeight() / 2 -
					$(window).scrollTop() - (this.jqElemContentContainer.outerHeight() / 2);
	};
	
	MultiAdd.prototype.applyFieldListMouseLeave = function() {
		var that = this;
		
		this.resetFieldListMouseLeave();

		this.jqElemContentContainer.on("mouseenter.multi-add", function() {
			that.applyFieldListMouseLeave();
		}).on("mouseleave.multi-add", function() {
			that.mouseLeaveTimeout = setTimeout(function() {
				that.hideList();
			}, 1000);
		}).on("click.multi-add", function(e) {
			e.stopPropagation();
		});
		
		$(window).on("keyup.multi-add", function(e) {
			if (e.which === 27) {
				//escape	
				that.hideList();	
			};
		}).on("click.multi-add", function() {
			that.hideList();
		});
	};
	
	MultiAdd.prototype.hideList = function() {
		this.jqElemContentContainer.hide();
		this.jqElemArrow.hide();
		this.resetFieldListMouseLeave();
	};
	
	MultiAdd.prototype.resetFieldListMouseLeave = function() {
		if (null !== this.mouseLeaveTimeout) {
			clearTimeout(this.mouseLeaveTimeout);
			this.mouseLeaveTimeout = null;
		}
		
		this.jqElemContentContainer.off("mouseenter.multi-add mouseleave.multi-add click.multi-add");
		$(window).off("keyup.multi-add click.multi-add");
	};
	
	(function() {
		var Filter = function(jqElem) {
			this.jqElem = jqElem;
			this.iconClassNameAdd = jqElem.data("icon-class-name-add");
			this.iconClassNameRemove = jqElem.data("remove-icon-class-name");
			this.iconClassNameAnd = jqElem.data("and-icon-class-name");
			this.iconClassNameOr = jqElem.data("or-icon-class-name");
			this.textAddGroup = jqElem.data("text-add-group");
			this.textAddField = jqElem.data("text-add-field");
			this.textOr = jqElem.data("text-or");
			this.textAnd = jqElem.data("text-and");
			this.textRemove = jqElem.data("text-delete");
			this.filterPropItemFormUrl = jqElem.data("filter-field-item-form-url");
			this.filterGroupFormUrl = jqElem.data("filter-group-form-url");
			this.fields = jqElem.data("filter-fields");
			
			new FilterGroup(jqElem.children(":first"), this, null);
		};
		
		Filter.prototype.requestFilterPropItem = function(filterGroup, fieldId, propertyPath, callback) {
			$.getJSON(this.filterPropItemFormUrl, {
				filterPropId: fieldId,
				propertyPath: propertyPath
			}, function(filterPropData) {
				var jqElemFilterPropItem = $($.parseHTML(n2n.dispatch.analyze(filterPropData))),
					filterPropItem = new FilterPropItem(jqElemFilterPropItem, filterGroup);
				callback(filterPropItem);
			});
		};
		
		Filter.prototype.requestFilterGroup = function(parentFilterGroup, propertyPath, callback) {
			var that = this;
			$.getJSON(this.filterGroupFormUrl, {
				propertyPath: propertyPath
			}, function(filterSettingGroup) {
				var jqElemFilterGroup = $($.parseHTML(n2n.dispatch.analyze(filterSettingGroup))),
					filterGroup = new FilterGroup(jqElemFilterGroup, that, parentFilterGroup);
				callback(filterGroup);
			});
		};
		
		Filter.prototype.getLabelForFieldId = function(fieldId) {
			if (!this.fields.hasOwnProperty(fieldId)) return;
			
			return this.fields[fieldId];
		};
		
		var FilterGroup = function(jqElem, filter, parentFilterGroup) {
			this.jqElem = jqElem;
			this.filter = filter;
			this.removable = (null !== parentFilterGroup);
			
			this.jqElemFieldItems = jqElem.find(".rocket-filter-field-items:first");
			this.nextFieldItemIndex = jqElem.children("li").length;
			this.baseFieldItemPropertyPath = this.jqElemFieldItems.data("new-form-array-property-path"); 
			
			this.jqElemGroups = jqElem.find(".rocket-filter-groups:first");
			this.nextGroupIndex = jqElem.children("li").length;
			this.baseGroupPropertyPath = this.jqElemGroups.data("new-form-array-property-path");
			
			this.jqElemCbxAndIndicator = jqElem.find(".rocket-filter-and-indicator:first").hide();
			
			this.jqElemDivCommands = null;
			this.jqElemSpanAndOrSwitchText = null;
			this.jqElemIAndOrSwitch = null;
			this.jqElemAAndOrSwitch = null;

			this.jqElemAAddFieldItem = null;
			this.jqElemAAddGroup = null;
			this.jqElemARemove = null;
			this.jqElemUlFieldsList = null;
			this.mouseLeaveTimeout = null;
			
			this.initializeCommands();
			
			(function(that) {
				this.jqElemAAndOrSwitch.click(function(e) {
					e.preventDefault();
					that.jqElemCbxAndIndicator.prop("checked", 
							!that.jqElemCbxAndIndicator.prop("checked"));
					that.applyAndOrSwitchTexts();
					that.applyAndOrSwitchIcons();
				});
				
				if (null !== parentFilterGroup) {
					this.jqElemSpanTextAndOr = $("<span />", {
						"text": parentFilterGroup.jqElemSpanAndOrSwitchText.text(),
						"class": "rocket-filter-field-text-and-or"
					}).appendTo(this.jqElem);

					parentFilterGroup.jqElemAAndOrSwitch.on("filter.changedText", function(e, text) {
						that.jqElemSpanTextAndOr.text(text);
					});
				}
				
				this.jqElemAAddGroup.click(function(e) {
					e.preventDefault();
					var jqElemLoading = $("<li />", {
						"class": "rocket-filter-group"
					}).append($("<div />", {
						"class": "rocket-loading"
					})).appendTo(that.jqElemGroups);
					
					filter.requestFilterGroup(that, that.buildNextGroupPropertyPath(), function(group) {
						jqElemLoading.remove();
						that.jqElemGroups.append(group.jqElem);
						that.jqElem.trigger('heightChange');
					});
				});
				
				this.jqElemFieldItems.children("li").each(function() {
					new FilterPropItem($(this), that);
				});
				
				this.jqElemGroups.children("li").each(function() {
					new FilterGroup($(this), filter, true);
				});
				
				this.applyAndOrSwitchTexts();
				this.applyAndOrSwitchIcons();
			}).call(this, this);
		};
		
		FilterGroup.prototype.initializeCommands = function() {
			var that = this;
			this.jqElemDivCommands = $("<div />", {
				"class": "btn-group rocket-filter-group-controls"
			}).insertAfter(this.jqElemGroups);
			
			this.jqElemSpanAndOrSwitchText = $("<span />");
			this.jqElemIAndOrSwitch = $("<i />");
			
			this.jqElemAAndOrSwitch = $("<a />", {
				"href": "#",
				"class": "rocket-control btn btn-secondary"
			}).append(this.jqElemIAndOrSwitch).append(this.jqElemSpanAndOrSwitchText)
			.appendTo(this.jqElemDivCommands);

			this.initializeMultiAdd();

			this.jqElemAAddGroup = $("<a />", {
				"href": "#",
				"class": "rocket-control btn btn-secondary"
			}).append($("<i />", {
				"class": this.filter.iconClassNameAdd
			})).append($("<span />", {
				"text": this.filter.textAddGroup
			})).appendTo(this.jqElemDivCommands);

			
			if (this.removable) {
				this.jqElemARemove = $("<a />", {
					"href": "#",
					"class": "rocket-control btn btn-secondary"
				}).append($("<i />", {
					"class": this.filter.iconClassNameRemove
				})).append($("<span />", {
					"text": this.filter.textRemove
				})).appendTo(this.jqElemDivCommands).click(function(e) {
					e.preventDefault();
					that.jqElem.remove();
					that.jqElem.trigger('heightChange');
				});
			}
		};
		
		FilterGroup.prototype.initializeMultiAdd = function() {
			var that = this;
			this.jqElemAAddFieldItem = $("<a />", {
				"href": "#",
				"class": "rocket-control btn btn-secondary"
			}).append($("<i />", {
				"class": this.filter.iconClassNameAdd
			})).append($("<span />", {
				"text": this.filter.textAddField
			})).appendTo(this.jqElemDivCommands);
			
			this.jqElemUlFieldsList = $("<ul />");
			
			var that = this;
			for (var fieldId in this.filter.fields) {
				$("<a />", {
					href: "#",
					text: this.filter.fields[fieldId]
				}).data("field-id", fieldId).click(function(e) {
					e.preventDefault();
					var jqElemLoading = $("<li />", {
						"class": "rocket-filter-field-item"
					}).append($("<div />", {
						"class": "rocket-loading"
					})).appendTo(that.jqElemFieldItems);
					
					that.filter.requestFilterPropItem(that, $(this).data("field-id"), that.buildNextFieldItemPropertyPath(), 
							function(fieldItem) {
						jqElemLoading.remove();
						that.jqElemFieldItems.append(fieldItem.jqElem);
						that.filter.jqElem.trigger('heightChange');
						n2n.dispatch.update();
					});
				}).appendTo($("<li />").appendTo(this.jqElemUlFieldsList));
			}
			
			new MultiAdd(this.jqElemAAddFieldItem, this.jqElemUlFieldsList);
		};
		
		FilterGroup.prototype.applyAndOrSwitchTexts = function() {
			if (this.jqElemCbxAndIndicator.prop("checked")) {
				this.jqElemSpanAndOrSwitchText.text(this.filter.textAnd);
			} else {
				this.jqElemSpanAndOrSwitchText.text(this.filter.textOr);
			}
			
			this.jqElemAAndOrSwitch.trigger("filter.changedText", [this.jqElemSpanAndOrSwitchText.text()]);
		};
		
		FilterGroup.prototype.applyAndOrSwitchIcons = function() {
			if (this.jqElemCbxAndIndicator.prop("checked")) {
				this.jqElemIAndOrSwitch.removeClass().addClass(this.filter.iconClassNameAnd);
			} else {
				this.jqElemIAndOrSwitch.removeClass().addClass(this.filter.iconClassNameOr);
			}
		};
		
		FilterGroup.prototype.buildNextFieldItemPropertyPath = function() {
			return this.baseFieldItemPropertyPath + '[' + this.nextFieldItemIndex++ + ']';
		};
		
		FilterGroup.prototype.buildNextGroupPropertyPath = function() {
			return this.baseGroupPropertyPath + '[' + this.nextGroupIndex++ + ']';
		};
		
		var FilterPropItem = function(jqElem, filterGroup) {
			this.jqElem = jqElem;
			this.filterGroup = filterGroup;
			this.jqElemSpanTextAndOr = $("<span />", {
				"text": filterGroup.jqElemSpanAndOrSwitchText.text(),
				"class": "rocket-filter-field-text-and-or"
			}).appendTo(this.jqElem);
			 
			this.jqElemARemove = $("<a />", {
				"href": "#",
				"class": "rocket-control rocket-filter-field-remove"
			}).append($("<i />", {
				"class": this.filterGroup.filter.iconClassNameRemove
			})).append($("<span />", {
				"text": this.filterGroup.filter.textRemove
			})).appendTo(this.jqElem);
			
			(function(that) {
				filterGroup.jqElemAAndOrSwitch.on("filter.changedText", function(e, text) {
					that.jqElemSpanTextAndOr.text(text);
				});
				
				this.jqElemARemove.click(function(e) {
					e.preventDefault();
					that.jqElem.remove();
					that.jqElem.trigger('heightChange');
				});
				
				var jqElemFieldId = jqElem.find(".rocket-filter-field-id:first");
				$("<span />", {
					"text": this.filterGroup.filter.getLabelForFieldId(jqElemFieldId.children("input").hide().val())
				}).appendTo(jqElemFieldId);
				
			}).call(this, this);
		};

		
		var initialize = function() {
			$(".rocket-filter").each(function() {
				var jqElem = $(this);
				if (jqElem.data("initialized-filter")) return;
				jqElem.data("initialized-filter", true);
				
				new Filter(jqElem);
			});
		};
		
		if (Jhtml) {
			Jhtml.ready(initialize);
		}
		
		initialize();
		n2n.dispatch.registerCallback(initialize);
	})();
	
	(function() {
		var Sort = function(jqElem) {
			this.jqElem = jqElem;
			this.jqElemSortConstraints = jqElem.find(".rocket-sort-contraints:first");
			this.textAddSort = jqElem.data("text-add-sort");
			this.iconClassNameAdd = jqElem.data("icon-class-name-add");
			this.textRemoveSort = jqElem.data("text-remove-sort");
			this.iconClassNameRemove = jqElem.data("icon-class-name-remove");
			this.jqElemEmpty = jqElem.find(".rocket-empty-sort-constraint").removeClass("rocket-empty-sort-constraint").detach();
			this.jqElemAdd = null;
			
			(function(that) {
				this.jqElemAdd = $("<a />", {
					"href": "#",
					"class": "btn btn-primary"
				}).append($("<i />", {
					"class": this.iconClassNameAdd
				})).append($("<span />", {
					"text": this.textAddSort
				})).appendTo(jqElem);
				
				this.jqElemUlFieldList = $("<ul />");
				this.jqElemEmpty.find(".rocket-sort-prop:first").children().each(function() {
					var jqElemOption = $(this);
					$("<li />").append($("<a />", {
						"href": "#",
						"text": jqElemOption.text()
					}).click(function(e) {
						e.preventDefault();
						//hide li
						$(this).parent().hide();
						that.deactivatePropName(jqElemOption.val());
						that.jqElemSortConstraints.append(that.requestSortConstraint(jqElemOption.val()));
					})).data("propName", jqElemOption.val()).appendTo(that.jqElemUlFieldList);
				});
				
				new MultiAdd(this.jqElemAdd, this.jqElemUlFieldList);
				if (this.jqElemUlFieldList.children.length === 0) {
					this.jqElemAdd.hide();
				}
				
				jqElem.find(".rocket-sort-constraint").each(function() {
					that.deactivatePropName(that.initializeSortItem($(this)));
				});
			}).call(this, this);
		};
		
		Sort.prototype.requestSortConstraint = function(propName) {
			var jqElem = this.jqElemEmpty.clone();
			
			this.initializeSortItem(jqElem, propName);
			return jqElem;
		};
		
		Sort.prototype.initializeSortItem = function(jqElem, propName) {
			var jqElemSortProp = jqElem.find(".rocket-sort-prop:first");
			if (propName) {
				jqElemSortProp.val(propName);
			} else {
				propName = jqElemSortProp.val();
			}
			
			jqElemSortProp.hide();
			var that = this;
			$("<span>", {
				"text": jqElemSortProp.children(":selected").text()
			}).prependTo(jqElem);
			var _obj = _obj;
			jqElem.append($("<a />", {
				"class": "rocket-control rocket-sort-constraint-remove",
				"href": "#"
			}).append($("<i />", {
				"class": "fa fa-times"
			}).append($("<span />", {
				"class": this.textRemoveSort
			}))).click(function(e) {
				e.preventDefault();
				that.activatePropName(propName);
				jqElem.remove();
			}));
			
			return propName;
		};
		
		Sort.prototype.activatePropName = function(propName) {
			this.jqElemUlFieldList.children().each(function() {
				if ($(this).data("propName") != propName) return;
				
				$(this).show();
			});
			
			this.jqElemAdd.show();
		};
		
		Sort.prototype.deactivatePropName = function(propName) {
			this.jqElemUlFieldList.children().each(function() {
				if ($(this).data("propName") != propName) return;
				
				$(this).hide();
			});
			
			if (this.jqElemUlFieldList.children(":visible").length === 0) {
				this.jqElemAdd.hide();
			}
		};
		
		var initialize = function() {
			$(".rocket-sort").each(function() {
				var jqElem = $(this);
				if (jqElem.data("initialized-sort")) return;
				jqElem.data("initialized-sort", true);
				
				new Sort(jqElem);
			});
		};
		
		if (Jhtml) {
			Jhtml.ready(initialize);
		}
		
		initialize();
		n2n.dispatch.registerCallback(initialize);
	})();
});
