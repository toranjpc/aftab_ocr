<template>
    <div>
        <v-card class="ma-2" width="110" flat :disabled="item.total_vehicles === 0"
            :color="item.total_vehicles < item.ocr_vehicles ? '#e9b5c859' : '#d8c8f547'" @click="getTransits()"
            :elevation="hover ? 4 : 2" @mouseover="hover = true" @mouseleave="hover = false"
            :class="{ 'cursor-pointer': true, 'transition-all': true, 'scale-105': hover }"
            style="transition: all 0.3s ease; border: 1px solid #e0e0e0;">

            <v-card-text class="text-center pa-2" :data-repetitive="item.bijacs?.[0]?.ocr_matches_count > 1 ? 1 : 0">
                <div class="text-h6">
                    <strong>{{ item.total_vehicles }}</strong>
                    <span class="mdi mdi-slash-forward" style="color:#9eafbe"></span>
                    <strong>{{ item.ocr_vehicles }}</strong>
                </div>
                <v-progress-circular :value="(item.ocr_vehicles / item.total_vehicles) * 100"
                    :color="item.total_vehicles === 0 ? '#d2d2d2' : item.total_vehicles < item.ocr_vehicles ? 'red' : 'purple'"
                    :class="{ 'animate__animated animate__heartBeat animate__infinite': item.total_vehicles < item.ocr_vehicles }"
                    size="50" width="4">
                    {{ item.total_vehicles < item.ocr_vehicles ? 'مازاد' : Math.round((item.ocr_vehicles /
                        item.total_vehicles) * 100) + '%' }} </v-progress-circular>
            </v-card-text>

            <v-overlay :value="hover" absolute opacity="0.1" color="purple">
            </v-overlay>
        </v-card>

        <v-dialog v-model="dialog">
            <v-card id="dialog">
                <v-card-title>
                    <span>ترددهای قبض انبار </span>
                    <v-btn small :color="'info'" class="black--text ma-1">
                        {{ item.invoice?.receipt_number }}
                    </v-btn>
                    <!-- <v-btn v-for="bijac in item.bijacs" small :color="'info'" class="black--text ma-1" :key="bijac.id">
                        {{ bijac.receipt_number }}
                    </v-btn> -->

                    <v-spacer />
                    <v-btn color="error" icon @click="dialog = false">
                        <v-icon>fal fa-times</v-icon>
                    </v-btn>
                </v-card-title>
                <v-card-text class="mt-4">
                    <TransitList :item="item" :fields="fields" />
                </v-card-text>
            </v-card>
        </v-dialog>
    </div>
</template>

<script>
import TransitList from './TransitList'
import createFields from './modalFields'

export default {
    components: {
        TransitList
    },
    props: ['item'],
    data() {
        return {
            dialog: false,
            hover: false,
            fields: createFields(this)
        }
    },

    methods: {
        getTransits() {
            if (this.item.total_vehicles !== 0) {
                this.dialog = true
            }
        }
    }
}
</script>
<style>
.v-card__text[data-repetitive="1"] {
    border: 4px solid #ffcc29 !important;
}

.v-card__text[data-repetitive="1"]:hover {
    border: 4px solid #e6b022 !important;
}
</style>