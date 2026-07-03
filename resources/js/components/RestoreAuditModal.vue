<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50" @click.self="handleClose">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden" style="width: 750px">
            <form @submit.prevent="handleConfirm">
                <div class="p-8">
                    <h2 class="text-2xl font-normal mb-6 text-gray-900 dark:text-gray-100">
                        {{ __('Restore audit') }}
                    </h2>
                    <table class="table w-full mt-4">
                        <thead>
                            <tr>
                                <th style="max-width: 20px;">
                                    <input type="checkbox" @change="toggleSelectAll" :checked="allSelected" />
                                </th>
                                <th class="text-left text-gray-500">{{ __('Field') }}</th>
                                <th class="text-left text-gray-500">{{ __('Current') }}</th>
                                <th class="text-left text-gray-500">{{ __('Restore to') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="compare in comparison" :key="compare.key" class="border-t border-gray-100 dark:border-gray-700">
                                <td style="max-width: 20px;" class="text-center">
                                    <input type="checkbox" class="checkbox" v-model="restoreIds" :value="compare.key" />
                                </td>
                                <td>{{ compare.label }}</td>
                                <td class="text-center text-red-500">{{ compare.current }}</td>
                                <td class="text-center text-green-500">{{ compare.restore }}</td>
                            </tr>
                            <tr v-if="comparison.length === 0">
                                <td colspan="4" class="text-center py-4 text-gray-500">
                                    {{ __('No changes') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="bg-gray-50 dark:bg-gray-700 px-6 py-3 flex justify-end space-x-3">
                    <Button
                        type="button"
                        @click.prevent="handleClose"
                        class="shrink-0 h-9 px-4 focus:outline-none ring-primary-200 dark:ring-gray-600 focus:ring text-gray-500 hover:text-gray-700 inline-flex items-center font-bold"
                    >
                        {{ __('Cancel') }}
                    </Button>

                    <Button
                        type="submit"
                        class="shrink-0 h-9 px-4 focus:outline-none ring-primary-200 dark:ring-gray-600 focus:ring text-white dark:text-gray-800 inline-flex items-center font-bold shadow rounded focus:outline-none ring-primary-200 dark:ring-gray-600 focus:ring bg-primary-500 hover:bg-primary-400 active:bg-primary-600 text-white dark:text-gray-800 inline-flex items-center font-bold px-4 h-9 text-sm shrink-0 h-9 px-4 focus:outline-none ring-primary-200 dark:ring-gray-600 focus:ring text-white dark:text-gray-800 inline-flex items-center font-bold"
                        :disabled="restoreIds.length === 0"
                    >
                        {{ __('Restore') }}
                    </Button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
export default {
    props: ['fields', 'audit', 'resourceName', 'resourceId'],

    data() {
        return {
            restoreIds: [],
            selectAll: false,
        };
    },

    methods: {
        handleClose() {
            this.$emit('close')
        },

        async handleConfirm() {
            if (this.restoreIds.length > 0) {
                const response = await Nova.request()
                    .post(`/nova-vendor/auditable-log/audits/${this.resourceName}/${this.resourceId}/${this.audit.id}`, { restore: this.restoreIds });

                const updatedProps = this.restoreIds.map(id => {
                    return {
                        key: id,
                        value: response.data.record[id]
                    }
                });

                this.$emit('restored', updatedProps);
            }

            this.$emit('close');
        },

        toggleSelectAll() {
            if (this.allSelected) {
                this.restoreIds = [];
                return;
            }

            this.restoreIds = this.comparison.map(c => c.key);
        }
    },

    computed: {
        allSelected() {
            return this.comparison.length === this.restoreIds.length;
        },
        comparison() {
            return Object.keys(this.audit.old_values).map(key => {
                if (typeof this.fields[key] === 'undefined') return null;
                if (this.fields[key].value === this.audit.old_values[key]) return null;

                return {
                    key,
                    label: this.fields[key].label,
                    current: this.fields[key].value,
                    restore: this.audit.old_values[key] !== null ? this.audit.old_values[key] : '—'
                }
            }).filter(field => field !== null)
        }
    },
}
</script>
