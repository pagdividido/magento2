/**
 * Copyright © Fluxx. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'mage/url',
        'Magento_Checkout/js/model/full-screen-loader',
        'Fluxx_Magento2/js/view/payment/method-renderer/jquery.mask'
    ],
    function ($, Component, urlBuild, fullScreenLoader, mask) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Fluxx_Magento2/payment/form',
                addtionalForm: 'Fluxx_Magento2/payment/addtional-form'
            },

            initObservable: function () {
                this._super()
                    .observe([
                        'rg',
                        'birthCity',
                        'birthRegion',
                        'financing',
                        'financingName',
                        'availability',
                        'dob'
                    ]);
                return this;
            },

            initialize: function () {
                this._super();
                var self = this;
                $('#fluxx_magento2_dob').mask("00/00/0000");
                this.dob.subscribe(function (value) {
                    var result;
                    self.getUpdateCreditRating();
                });
            },

            getUpdateCreditRating: function() {
                var self = this;
                var form_key = $.cookie('form_key');
                var urlCheck = urlBuild.build("fluxx/CreditRating/check/form_key/"+form_key);
                $.ajax({
                    url: urlCheck,
                    dataType: 'json',
                    timeout: 55000,
                    type : 'post',
                    data: { dob: $('#fluxx_magento2_dob').val() },
                    beforeSend : function(){
                      $("#fluxx_magento2_button").show();
                      $(".messages-not-availability").html("");
                      $("#fluxx_magento2_financing_name_global").show();
                      fullScreenLoader.startLoader();
                    }
                }).done(function (data) {
                    if(data.availability) {
                         $("#fluxx_magento2_financing").html("");
                         $("#fluxx_magento2_financing").append('<option>Escolha seu parcelamento</option>');
                        _.map(data.offers, function(value, key) {
                            $("#fluxx_magento2_financing").append('<option value="'+key+'">'+value+'</option>');
                        });
                        $("#fluxx_magento2_financing_name").html(data.institution);
                        $("#fluxx_magento2_financing_name_value").val(data.institution);
                    } else {
                        $("#fluxx_magento2_button").hide();
                        $("#fluxx_magento2_financing_name_global").hide();
                        $(".messages-not-availability").append('No momento não foi possível liberar crédito para você.');
                    }
                    fullScreenLoader.stopLoader();
                    return data;
                }).fail(function(data){
                    $("#fluxx_magento2_button").hide();
                    $("#fluxx_magento2_financing_name_global").hide();
                    $(".messages-not-availability").append('No momento não foi possível liberar crédito para você.');
                    fullScreenLoader.stopLoader();
                });
            },

            getFinancing: function() {
                return _.map(window.checkoutConfig.payment.fluxx_magento2.checkOffers.offers, function(value, key) {
                    return {
                        'value': key,
                        'financing': value
                    }
                });
            },
            getFinancingName: function() {
                return window.checkoutConfig.payment.fluxx_magento2.checkOffers.institution;
            },

            getLogoUrl: function() {
                return window.checkoutConfig.payment.fluxx_magento2.logo;
            },
            getIconHtml: function () {
                return '<img src="' + this.getLogoUrl() +
                    '" alt="Fluxx" title="Boleto Parcelado" />';
            },
            getCode: function() {
                return 'fluxx_magento2';
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
                    'method': this.item.method,
                    'additional_data': {
                        'rg':this.rg(),
                        'birth_city': this.birthCity(),
                        'birth_region': this.birthRegion(),
                        'financing': this.financing(),
                        'financing_name': $('#'+this.getCode()+'_financing_name_value').val(),
                        'availability': this.availability(),
                        'dob': this.dob()
                    }
                };
            },

            getBirthRegion: function() {
                return _.map(window.checkoutConfig.payment.fluxx_magento2.birthRegion, function(value, key) {
                    return {
                        'value': key,
                        'birth_region': value
                    }
                });
            },

            getAddtionalAvailability: function() {
                return window.checkoutConfig.payment.fluxx_magento2.checkOffers.availability;
            }
          

        });
    }
);