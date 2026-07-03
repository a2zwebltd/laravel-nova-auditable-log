<template>
    <div>
        <div v-if="displayAudits">
            <div class="flex flex-row items-center justify-between">
                <h2 class="mb-3 text-90 font-normal text-2xl">{{ __('Audit Log') }}</h2>
                <Button
                    class="shrink-0 h-9 px-4 focus:outline-none ring-primary-200 dark:ring-gray-600 focus:ring text-white dark:text-gray-800 inline-flex items-center font-bold shadow rounded focus:outline-none ring-primary-200 dark:ring-gray-600 focus:ring bg-primary-500 hover:bg-primary-400 active:bg-primary-600 text-white dark:text-gray-800 inline-flex items-center font-bold px-4 h-9 text-sm shrink-0 h-9 px-4 focus:outline-none ring-primary-200 dark:ring-gray-600 focus:ring text-white dark:text-gray-800 inline-flex items-center font-bold ml-4 mb-2"
                    @click.prevent="close"
                    v-if="displayAudits"
                >
                    {{ __('Close Audit Log') }}
                </Button>
            </div>
            <div class="card bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <table data-testid="resource-table" class="table w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th></th>
                        <th class="text-left text-gray-500 dark:text-gray-400 py-2"><span> {{ __('User') }} </span></th>
                        <th class="text-left text-gray-500 dark:text-gray-400 py-2"><span> {{ __('Event') }} </span></th>
                        <th class="text-left text-gray-500 dark:text-gray-400 py-2"><span> {{ __('Date/Time') }} </span></th>
                        <th class="text-left text-gray-500 dark:text-gray-400 py-2"><span> {{ __('Old Values') }} </span></th>
                        <th class="text-left text-gray-500 dark:text-gray-400 py-2"><span> {{ __('New Values') }} </span></th>
                        <th class="text-gray-500 py-2" v-if="canRestore"></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr class="group bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700" v-for="audit in audits" :key="audit.id">
                        <td class="py-2 border-t border-gray-100 dark:border-gray-800">
                            <div class="px-4">
                                <svg v-if="audit.event === 'created'" aria-hidden="true" focusable="false" data-prefix="fas"
                                     data-icon="plus-circle" class="w-5 h-5 text-green-500" role="img" xmlns="http://www.w3.org/2000/svg"
                                     viewBox="0 0 512 512">
                                    <path fill="currentColor"
                                          d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8zm144 276c0 6.6-5.4 12-12 12h-92v92c0 6.6-5.4 12-12 12h-56c-6.6 0-12-5.4-12-12v-92h-92c-6.6 0-12-5.4-12-12v-56c0-6.6 5.4-12 12-12h92v-92c0-6.6 5.4-12 12-12h56c6.6 0 12 5.4 12 12v92h92c6.6 0 12 5.4 12 12v56z">
                                    </path>
                                </svg>
                                <svg v-if="audit.event === 'updated'" aria-hidden="true" focusable="false" data-prefix="fas"
                                     data-icon="sync-alt" class="w-5 h-5 text-blue-500" role="img" xmlns="http://www.w3.org/2000/svg"
                                     viewBox="0 0 512 512">
                                    <path fill="currentColor"
                                          d="M370.72 168.44A192 192 0 1 0 430 318.53H384a146 146 0 1 1-45.18-111.45L312 233.91H448v-136z">
                                    </path>
                                </svg>
                                <svg v-if="audit.event === 'deleted'" aria-hidden="true" focusable="false" data-prefix="fas"
                                     data-icon="trash-alt" class="w-5 h-5 text-red-500" role="img" xmlns="http://www.w3.org/2000/svg"
                                     viewBox="0 0 448 512">
                                    <path fill="currentColor"
                                          d="M32 464a48 48 0 0 0 48 48h288a48 48 0 0 0 48-48V128H32zm272-256a16 16 0 0 1 32 0v224a16 16 0 0 1-32 0zm-96 0a16 16 0 0 1 32 0v224a16 16 0 0 1-32 0zm-96 0a16 16 0 0 1 32 0v224a16 16 0 0 1-32 0zM432 32H312l-9.4-18.7A24 24 0 0 0 281.1 0H166.8a23.72 23.72 0 0 0-21.4 13.3L136 32H16A16 16 0 0 0 0 48v32a16 16 0 0 0 16 16h416a16 16 0 0 0 16-16V48a16 16 0 0 0-16-16z">
                                    </path>
                                </svg>
                            </div>
                        </td>
                        <td class="py-2 border-t border-gray-100 dark:border-gray-800">
                            {{ audit.user ? audit.user.name : __('console') }}
                        </td>
                        <td class="py-2 border-t border-gray-100 dark:border-gray-800">
                            {{ audit.event }}
                        </td>
                        <td class="py-2 border-t border-gray-100 dark:border-gray-800">
                            {{ audit.created_at }}
                        </td>
                        <td class="py-2 border-t border-gray-100 dark:border-gray-800">
                            <div v-for="old_value in formatData(audit.old_values)" :key="old_value.name" class="my-2">
                                <span class="inline-block bg-gray-100 dark:bg-gray-700 p-1 rounded-sm mr-2 font-bold">{{ old_value.name }}</span> {{
                                    old_value.value
                                }}
                            </div>
                        </td>
                        <td class="py-2 border-t border-gray-100 dark:border-gray-800">
                            <div v-for="new_value in formatData(audit.new_values)" :key="new_value.name" class="my-2">
                                <span class="inline-block bg-gray-100 dark:bg-gray-700 p-1 rounded-sm mr-2 font-bold">{{ new_value.name }}</span> {{
                                    new_value.value
                                }}
                            </div>
                        </td>
                        <td class="py-2 text-center border-t border-gray-100 dark:border-gray-800"
                            v-if="canRestore">
                            <svg @click="showRestoreAudit(audit)" style="max-width: 20px;"
                                 xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:cc="http://creativecommons.org/ns#"
                                 xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
                                 xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg"
                                 xmlns:sodipodi="http://sodipodi.sourceforge.net/DTD/sodipodi-0.dtd"
                                 xmlns:inkscape="http://www.inkscape.org/namespaces/inkscape" viewBox="0 -256 1792 1792"
                                 id="svg3447" version="1.1" inkscape:version="0.48.3.1 r9886" width="100%" height="100%"
                                 sodipodi:docname="undo_font_awesome.svg"
                                 class="cursor-pointer text-gray-500 hover:text-gray-700 w-5 h-5 mx-auto">
                                <defs id="defs3455"/>
                                <sodipodi:namedview pagecolor="#ffffff" bordercolor="#666666" borderopacity="1"
                                                    objecttolerance="10" gridtolerance="10" guidetolerance="10"
                                                    inkscape:pageopacity="0" inkscape:pageshadow="2"
                                                    inkscape:window-width="640" inkscape:window-height="480"
                                                    id="namedview3453" showgrid="false" inkscape:zoom="0.13169643"
                                                    inkscape:cx="896" inkscape:cy="896" inkscape:window-x="0"
                                                    inkscape:window-y="25" inkscape:window-maximized="0"
                                                    inkscape:current-layer="svg3447"/>
                                <g transform="matrix(1,0,0,-1,113.89831,1262.6441)" id="g3449">
                                    <path
                                        d="M 1536,640 Q 1536,484 1475,342 1414,200 1311,97 1208,-6 1066,-67 924,-128 768,-128 589,-128 431.5,-52 274,24 165.5,161 57,298 18,473 q -3,14 7,27 9,12 25,12 h 199 q 23,0 30,-23 Q 329,327 464,227.5 599,128 768,128 872,128 966.5,168.5 1061,209 1130,278 q 69,69 109.5,163.5 40.5,94.5 40.5,198.5 0,104 -40.5,198.5 Q 1199,933 1130,1002 1061,1071 966.5,1111.5 872,1152 768,1152 670,1152 580,1116.5 490,1081 420,1015 L 557,877 q 31,-30 14,-69 -17,-40 -59,-40 H 64 Q 38,768 19,787 0,806 0,832 v 448 q 0,42 40,59 39,17 69,-14 l 130,-129 q 107,101 244.5,156.5 137.5,55.5 284.5,55.5 156,0 298,-61 142,-61 245,-164 103,-103 164,-245 61,-142 61,-298 z"
                                        id="path3451" inkscape:connector-curvature="0" style="fill:currentColor"/>
                                </g>
                            </svg>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <div class="bg-20 rounded-b p-3 border-t border-gray-100 dark:border-gray-800" v-if="pagination.total > 0">
                    <nav class="flex justify-between items-center">
                        <button :disabled="pagination.prev_page_url === null" rel="prev" dusk="previous"
                                class="btn btn-link py-3 px-4 text-80"
                                :class="{ 'opacity-50': pagination.prev_page_url === null, 'text-primary': pagination.prev_page_url !== null }"
                                @click="fetchAudits(pagination.prev_page_url)">
                            {{ __('Previous') }}
                        </button>
                        <span class="text-sm text-80 px-4">
                    {{ pagination.from }}-{{ pagination.to }} of {{ pagination.total }}
                </span>
                        <button :disabled="pagination.next_page_url === null" rel="next" dusk="next"
                                :class="{ 'opacity-50': pagination.next_page_url === null, 'text-primary': pagination.next_page_url !== null }"
                                class="btn btn-link py-3 px-4 text-80" @click="fetchAudits(pagination.next_page_url)">
                            {{ __('Next') }}
                        </button>
                    </nav>
                </div>
            </div>
        </div>

        <Button
            @click.prevent="showAndFetch"
            v-if="displayAudits === false"
            class="shrink-0 h-9 px-4 focus:outline-none ring-primary-200 dark:ring-gray-600 focus:ring text-white dark:text-gray-800 inline-flex items-center font-bold shadow rounded focus:outline-none ring-primary-200 dark:ring-gray-600 focus:ring bg-primary-500 hover:bg-primary-400 active:bg-primary-600 text-white dark:text-gray-800 inline-flex items-center font-bold px-4 h-9 text-sm shrink-0 h-9 px-4 focus:outline-none ring-primary-200 dark:ring-gray-600 focus:ring text-white dark:text-gray-800 inline-flex items-center font-bold mt-4"
        >
            {{ __('View Audit Log') }}
        </Button>

        <restore-audit-modal v-if="restore !== null" :fields="parentFields" :resourceName="resourceName"
                             :resourceId="resourceId" :audit="restore" @close="restore = null" @restored="restored">
        </restore-audit-modal>
    </div>
</template>

<script>
import RestoreAuditModal from './RestoreAuditModal';
import {normaliseFields} from './../fields';

export default {
    props: ['resourceName', 'resourceId', 'field'],

    components: {
        RestoreAuditModal
    },
    data() {
        return {
            audits: [],
            displayAudits: false,
            pagination: {},
            restore: null,
            parentFields: {},
            canRestore: false,
        }
    },

    mounted() {
        if (this.displayAudits === true) {
            this.fetchAudits();
        }

        // Normalise the parent fields safely for Nova 5!
        let rawFields = [];
        try {
            let parent = this.$parent;
            while (parent) {
                if (parent.resource && parent.resource.fields) {
                    rawFields = parent.resource.fields;
                    break;
                }
                if (parent.fields) {
                    rawFields = parent.fields;
                    break;
                }
                parent = parent.$parent;
            }
        } catch (e) {
            console.error('Failed to auto-detect parent fields:', e);
        }

        this.parentFields = normaliseFields(rawFields);
    },

    methods: {

        showAndFetch() {
            this.displayAudits = true;
            this.fetchAudits();
        },

        close() {
            this.displayAudits = false;
        },

        async fetchAudits(page = null) {
            if (!page) {
                page = `/nova-vendor/auditable-log/audits/${this.resourceName}/${this.resourceId}`
            }

            try {
                const {data} = await Nova.request().get(page);
                this.audits = data.audits.data;
                this.pagination = data.audits;
                this.canRestore = data.restore;
            } catch (e) {
                // Do nothing, nova handles errors
            }
        },

        formatData(values) {
            let returned = [];

            for (var property in values) {
                if (values.hasOwnProperty(property)) {
                    returned.push({name: property, value: values[property]});
                }
            }

            return returned;
        },

        showRestoreAudit(audit) {
            this.restore = audit;
        },

        restored(updatedProps) {
            // Updates the value of the parent fields
            if (updatedProps) {
                updatedProps.forEach(prop => {
                    if (this.parentFields[prop.key]) {
                        this.parentFields[prop.key].value = prop.value;
                    }
                });
            }

            // Retrieve fresh audits
            this.fetchAudits();
            
            // Hard reload parent view so it reflects the changes on the page immediately!
            window.location.reload();
        }
    }
}
</script>
