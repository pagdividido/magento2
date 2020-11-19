/**
 * Copyright © PagDividido. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        "underscore",
        "jquery",
        "Magento_Checkout/js/view/payment/default",
        "mage/url",
        "Magento_Checkout/js/model/full-screen-loader",
        "pagdividido_magento2/js/view/payment/method-renderer/jquery.mask",
        "mage/translate"
    ],
    function (_, $, Component, urlBuild, fullScreenLoader, mask, $t) {
        "use strict";

        return Component.extend({
            defaults: {
                template: "pagdividido_magento2/payment/form",
                addtionalForm: "pagdividido_magento2/payment/addtional-form"
            },

            initObservable: function () {
                this._super()
                    .observe([
                        "rg",
                        "birthCity",
                        "birthRegion",
                        "financing",
                        "financingName",
                        "availability",
                        "dob"
                    ]);
                return this;
            },

            initialize: function () {
                this._super();
                var self = this;
                $("#pagdividido_magento2_dob").mask("00/00/0000");
                this.dob.subscribe(function (date) {
                    self.getUpdateCreditRating(date);
                });
            },

            getUpdateCreditRating: function(date) {
                var self = this;
                var form_key = $.cookie("form_key");
                var urlCheck = urlBuild.build("PagDividido/CreditRating/check/form_key/"+form_key);
                var datearray = date.split("/");
                date = datearray[1] + '/' + datearray[0] + '/' + datearray[2];
                $.ajax({
                    url: urlCheck,
                    dataType: "json",
                    timeout: 55000,
                    type : "post",
                    data: { dob: date},
                    beforeSend : function(){
                      $("#pagdividido_magento2_button").show();
                      $(".messages-availability-messages").hide().removeClass("message error notice success");
                      $("#pagdividido_magento2_financing_name_global").show();
                      fullScreenLoader.startLoader();
                    }
                }).done(function (data) {
                    if(data.availability) {
                        self.setAvailability();
                         $("#pagdividido_magento2_financing").html("");
                         var optionsCaption = "<option>" + $t("Select the installment offer") +"</option>";
                         $("#pagdividido_magento2_financing").append(self.escapeHTML(optionsCaption));
                        _.map(data.offers, function(value, key) {
                            var options = "<option value="+key+">"+value+"</option>";
                            $("#pagdividido_magento2_financing").append(self.escapeHTML(options));
                        });
                        $("#pagdividido_magento2_financing_name").html(data.institution);
                        $("#pagdividido_magento2_financing_name_value").val(data.institution);
                    } else if (data.conditionalAvailability){
                       self.setPartialAvailability(data.conditionalValue);
                    } else 
                    {
                        self.setNotAvailability();
                     }
                    fullScreenLoader.stopLoader();
                    return data;
                }).fail(function(data){
                    self.setNotAvailability();
                    fullScreenLoader.stopLoader();
                });
            },

            setNotAvailability: function() {
                $("#pagdividido_magento2_button").hide();
                $("#pagdividido_magento2_financing_name_global").hide();
                $(".messages-availability-messages").show().addClass("error message");
                $(".messages-availability-text").html("");
                $(".messages-availability-text").append($t("It is currently not possible to obtain credit offers."));
            },

            setAvailability: function() {
                $(".messages-availability-messages").show().addClass("success message");
                $(".messages-availability-text").html("");
                $(".messages-availability-text").append($t("Great! Your credit has been pre-approved. Please select the amount of installments and finish your order."));
            },

            setPartialAvailability: function(preapprovedvalue) {
                $("#pagdividido_magento2_button").hide();
                $("#pagdividido_magento2_financing_name_global").hide();
                $(".messages-availability-messages").show().addClass("notice message");
                $(".messages-availability-text").html("");
                $(".messages-availability-text").append($t("Pre-approved value of US$ (preapprovedvalue). Please change your order total value to procced").replace('(preapprovedevalue)', preapprovedvalue));
            },

            getFinancingName: function() {
                return window.checkoutConfig.payment.pagdividido_magento2.checkOffers.institution;
            },

            getLogoUrl: function() {
                return window.checkoutConfig.payment.pagdividido_magento2.logo;
            },
            getIconHtml: function () {
                return '<img src="' + this.getLogoUrl() +
                    '" alt="PagDividido" title="Credito Digital" />';
            },
            getCode: function() {
                return "pagdividido_magento2";
            },

            initFormElement: function (element) {
                this.formElement = element;
                $(this.formElement).validation();
            },

            beforePlaceOrder: function () {
                if (!$(this.formElement).valid()) {
                    return;
                } else {
                     this.placeOrder();
                }
            },
            
            getData: function() {
                return {
                    "method": this.item.method,
                    "additional_data": {
                        "rg":this.rg(),
                        "birth_city": this.birthCity(),
                        "birth_region": this.birthRegion(),
                        "financing": this.financing(),
                        "financing_name": $("#"+this.getCode()+"_financing_name_value").val(),
                        "availability": this.availability(),
                        "dob": this.dob()
                    }
                };
            },

            getBirthRegion: function() {
                return _.map(window.checkoutConfig.payment.pagdividido_magento2.birthRegion, function(value, key) {
                    return {
                        "value": key,
                        "birth_region": value
                    }
                });
            },

            getAddtionalAvailability: function() {
                return window.checkoutConfig.payment.PagDividido_magento2.checkOffers.availability;
            },

            escapeHTML: function (str) {
                return str ? str.replace(/"/g, "&quot;") : "";
            }
          

        });
    }
);