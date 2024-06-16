<template>
    <div>
        <Head title="Products To Shop Uploader" />

        <Heading class="mb-6">Products To Shop Uploader</Heading>

        <Card class="flex flex-col py-5 px-5" style="min-height: 300px">
            <form @submit.prevent="uploadProduct">
                <div class="space-y-2 md:flex @md/modal:flex md:flex-row @md/modal:flex-row md:space-y-0 @md/modal:space-y-0 py-5">
                    <div class="w-full px-6 md:mt-2 @md/modal:mt-2 md:px-8 @md/modal:px-8 md:w-1/5 @md/modal:w-1/5">
                        <label for="sku-nova-field" class="inline-block leading-tight space-x-1">
                            <span>SKU</span>
                            <span class="text-red-500 text-sm">*</span>
                        </label>
                    </div>
                    <div class="w-full space-y-2 px-6 md:px-8 @md/modal:px-8 md:w-3/5 @md/modal:w-3/5">
                        <div class="flex items-center">
                            <input class="w-full form-control form-input form-control-bordered" id="sku-nova-field"  v-model="sku" required>
                        </div>
                    </div>
                </div>
                <div class="space-y-2 md:flex @md/modal:flex md:flex-row @md/modal:flex-row md:space-y-0 @md/modal:space-y-0 py-5">
                    <div class="w-full px-6 md:mt-2 @md/modal:mt-2 md:px-8 @md/modal:px-8 md:w-1/5 @md/modal:w-1/5">
                        <label for="sizes-nova-field" class="inline-block leading-tight space-x-1">
                            <span>Sizes (comma separated)</span>
                            <span class="text-red-500 text-sm">*</span>
                        </label>
                    </div>
                    <div class="w-full space-y-2 px-6 md:px-8 @md/modal:px-8 md:w-3/5 @md/modal:w-3/5">
                        <div class="flex items-center">
                            <input class="w-full form-control form-input form-control-bordered" id="sizes-nova-field"  v-model="sizes" required>
                        </div>
                    </div>
                </div>
                <div class="space-y-2 md:flex @md/modal:flex md:flex-row @md/modal:flex-row md:space-y-0 @md/modal:space-y-0 py-5">
                    <div class="w-full px-6 md:mt-2 @md/modal:mt-2 md:px-8 @md/modal:px-8 md:w-1/5 @md/modal:w-1/5">
                        <label for="prices-nova-field" class="inline-block leading-tight space-x-1">
                            <span>Prices (comma separated)</span>
                            <span class="text-red-500 text-sm">*</span>
                        </label>
                    </div>
                    <div class="w-full space-y-2 px-6 md:px-8 @md/modal:px-8 md:w-3/5 @md/modal:w-3/5">
                        <div class="flex items-center">
                            <input class="w-full form-control form-input form-control-bordered" id="prices-nova-field"  v-model="prices" required>
                        </div>
                    </div>
                </div>
                <div class="space-y-2 md:flex @md/modal:flex md:flex-row @md/modal:flex-row md:space-y-0 @md/modal:space-y-0 py-5">
                    <div class="w-full px-6 md:mt-2 @md/modal:mt-2 md:px-8 @md/modal:px-8 md:w-1/5 @md/modal:w-1/5">
                        <label for="presence-nova-field" class="inline-block leading-tight space-x-1">
                            <span>Presence (comma separated)</span>
                            <span class="text-red-500 text-sm">*</span>
                        </label>
                    </div>
                    <div class="w-full space-y-2 px-6 md:px-8 @md/modal:px-8 md:w-3/5 @md/modal:w-3/5">
                        <div class="flex items-center">
                            <input class="w-full form-control form-input form-control-bordered" id="presence-nova-field"  v-model="presence" required>
                        </div>
                    </div>
                </div>

                <button type="submit" class="border text-left appearance-none cursor-pointer rounded text-sm font-bold focus:outline-none focus:ring ring-primary-200 dark:ring-gray-600 relative disabled:cursor-not-allowed inline-flex items-center justify-center shadow h-9 px-3 bg-primary-500 border-primary-500 hover:[&:not(:disabled)]:bg-primary-400 hover:[&:not(:disabled)]:border-primary-400 text-white dark:text-gray-900 ml-3">
                    <span v-if="!loading">Upload Product</span>
                    <span v-else>Loading...</span>
                </button>
            </form>

            <div v-if="responseMessage" class="mt-6">
                <p v-if="responseSuccess" class="text-green-600">{{ responseMessage }}</p>
                <p v-else class="text-red-600">{{ responseMessage }}</p>
            </div>

        </Card>
    </div>
</template>

<script>
export default {
    data() {
        return {
            sku: '',
            sizes: '',
            prices: '',
            presence: '',
            responseMessage: '',
            responseSuccess: false,
            loading: false
        };
    },
    methods: {
        async uploadProduct() {
            this.loading = true;

            try {
                const response = await Nova.request().post('/nova-custom-api/store-product-to-shop', {
                    sku: this.sku,
                    sizes: this.sizes,
                    prices: this.prices,
                    presence: this.presence,
                });

                this.responseMessage = response.data;
                this.responseSuccess = true;
                this.resetForm();

                this.loading = false;

            } catch (error) {
                this.responseMessage = error.response.data.message || 'An error occurred. Please try again later.';
                this.responseSuccess = false;
                this.loading = false;
            }
        },
        resetForm() {
            this.sku = '';
            this.sizes = '';
            this.prices = '';
            this.presence = '';
        }
    }
}
</script>

<style scoped>
/* Scoped Styles */
</style>
