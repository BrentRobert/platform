import template from './sw-sales-channel-google-website-verification.html.twig';
import './sw-sales-channel-google-website-verification.scss';

const { Component } = Shopware;

Component.register('sw-sales-channel-google-website-verification', {
    template,

    props: {
        salesChannel: {
            type: Object,
            required: true
        }
    },

    data() {
        return {
            websisteUrl: '',
            storeName: '',
            businessLocation: '',
            hasAdultContent: false
        };
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.updateButtons();
        },

        updateButtons() {
            const buttonConfig = {
                right: {
                    label: this.$tc('sw-sales-channel.modalGooglePrograms.buttonNext'),
                    variant: 'primary',
                    action: this.onClickNext,
                    disabled: false
                },
                left: {
                    label: this.$tc('sw-sales-channel.modalGooglePrograms.buttonBack'),
                    variant: null,
                    action: 'sw.sales.channel.detail.base.step-4',
                    disabled: false
                }
            };

            this.$emit('buttons-update', buttonConfig);
        },

        onClickNext() {
            this.$router.push({ name: 'sw.sales.channel.detail.base.step-6' });
        }
    }
});
