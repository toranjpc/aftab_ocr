<template>
	<div class="my-5">
		<v-card class="py-3" style="background-color: #fff3d0">
			<v-card-text>
				<h3 class="font-weight-bold">
					<v-badge :class="'me-3 animate__animated animate__heartBeat animate__infinite'" color="success" dot
						location="top start" transition="scale-transition">
						<!-- <v-icon size="x-large">fal fa-cctv</v-icon>	  -->
					</v-badge>
					جاری
				</h3>
				<div class="row mt-2">
					<div class="col-6 col-md-2">
						<h4 class="font-weight-bold mb-2">پلاک</h4>
						<div style="min-height:40px">
							<div v-html="plateShow(item.plate_number, item)"></div>
						</div>
						<div style="min-height:45px">
							<img v-if="item.plate_image_url" class="resizable"
								:style="'border-radius:10px;margin-top:5px;max-width: 120px;'"
								:src="url + item.plate_image_url" />
						</div>
						<EditBtn
							v-if="['container_without_bijac', 'no_bijac', 'gcoms_nok', 'miss_container_ccs_nok'].includes(item.match_status)"
							:editItem="item" :fields="plateFields(item)" />
					</div>

					<div class="col-6 col-md-2">
						<h4 class="font-weight-bold mb-2">کد کانتینر</h4>
						<div style="min-height:54px">
							<div v-html="containerCodeShow(item.container_code, item)"></div>
						</div>
						<div style="min-height:48px">
							<img v-if="item.container_code_image_url" class="resizable"
								:style="'border-radius:10px;margin-top:5px;max-width: 120px;'"
								:src="url + item.container_code_image_url" />
						</div>
						<EditBtn
							v-if="['container_without_bijac', 'no_bijac', 'miss_container_ccs_nok'].includes(item.match_status)"
							:editItem="item" :fields="containerFields(item)" />
					</div>

					<div class="col-6 col-md-3">
						<h4 class="font-weight-bold mb-2">تصاویر دوربین</h4>
						<div class="d-flex flex-row">
							<div class="ms-2">
								<img v-if="item.vehicle_image_front_url" class="resizable"
									:style="'border-radius:10px;margin-top:5px;max-width: 120px;'"
									:src="url + item.vehicle_image_front_url" />
							</div>
							<div class="ms-2">
								<img v-if="item.vehicle_image_back_url" class="resizable"
									:style="'border-radius:10px;margin-top:5px;max-width: 120px;'"
									:src="url + item.vehicle_image_back_url" />
							</div>

						</div>
					</div>

					<div class="col-6 col-md-2">
						<h4 class="font-weight-bold mb-2">کالای خطرناک</h4>
						<div>
							<div class="rounded px-1 py-1" style="height:30px;background:#fff">
								<h5>{{ item.IMDG == 0 || item.IMDG == '' ? 'غیرخطرناک' : 'خطرناک' }}</h5>
							</div>
						</div>
						<h4 class="font-weight-bold mb-2 mt-4">پلمپ</h4>
						<div class="rounded px-1 py-1" style="height:30px;background:#fff">
							<h5>{{ item.seal ?? '--' }}</h5>
						</div>
					</div>

					<div class="col-6 col-md-2">
						<h4 class="font-weight-bold mb-2">تاریخ لاگ</h4>
						<div class="rounded px-1 py-1" style="height:30px;background:#fff">
							<h5 class="text-right ltr">{{ new Date(item.log_time).toLocaleString('fa-IR') }}</h5>
						</div>
						<h4 class="font-weight-bold mb-2 mt-4">وضعیت</h4>
						<div class="rounded px-1 py-1" style="height:30px;background:#fff">
							<h5>{{ item.match_status }}</h5>
						</div>
					</div>

					<div class="col-6 col-md-1">
						<h4 class="text-center font-weight-bold mb-2">عملیات</h4>
						<div class="d-flex flex-column">
							<v-tooltip small bottom>
								<template v-slot:activator="{ on, attrs }">
									<v-btn v-bind="attrs" v-on="on" text x-small @click="_event('showBtn', item)">
										<v-icon color="primary" small>mdi-eye</v-icon>
									</v-btn>
								</template>

								<span>{{ translate("Show details") }}</span>
							</v-tooltip>

							<v-tooltip small bottom>
								<template v-slot:activator="{ on, attrs }">
									<v-btn text v-on="on" v-bind="attrs" x-small @click="_event('editBtn', item)">
										<v-icon small color="info">mdi-square-edit-outline</v-icon>
									</v-btn>
								</template>

								<span>{{ translate("Edit") }}</span>
							</v-tooltip>

							<v-tooltip small bottom>
								<template v-slot:activator="{ on, attrs }">
									<v-btn v-on="on" v-bind="attrs" text x-small @click="_event('deleteBtn', item.id)">
										<v-icon color="error" small>mdi-delete</v-icon>
									</v-btn>
								</template>

								<span>{{ translate("Delete") }}</span>
							</v-tooltip>
						</div>
					</div>

				</div>
			</v-card-text>
		</v-card>
	</div>
</template>

<script>
import NormalizeVehicleNumberAsImg from '@/helpers/NormalizeVehicleNumberAsImg'
import NormalizeContainerCodeAsImg from '@/helpers/NormalizeContainerCodeAsImg'
import EditBtn from '@/components/utilities/EditBtn'
import PlateField from '@/components/utilities/PlateField'

export default {
	components: { EditBtn },

	props: {
		item: { type: Object, default: () => { } },
	},

	computed: {
		url() {
			return process.env.baseURL
			// return 'http://46.148.36.110:8000/ocrbackend/'
		},
	},

	methods: {

		plateShow(v, form) {
			let concat = ''

			if (form.plate_number_2 && form.plate_number_2 != v)
				concat =
					'</br>' +
					NormalizeVehicleNumberAsImg(
						form.plate_number_2 || '',
						form.plate_type
					)

			return (
				NormalizeVehicleNumberAsImg(
					form.plate_number_edit || v || '',
					form.plate_type,
					!!form.plate_number_edit
				) + concat
			)
		},
		containerCodeShow(v, form) {
			let concat = ''

			if (form.container_code_2 && form.container_code_2 != v)
				concat = '</br>' + NormalizeContainerCodeAsImg(form.container_code_2)

			if (v) {
				return (
					NormalizeContainerCodeAsImg(
						form.container_code_edit || v,
						form.container_code_edit ? 'green' : '#2957a4'
					) + concat
				)
			}

			return '-'
		},

		plateFields: (item) => [
			{
				title: 'شماره پلاک',
				field: 'plate_number_edit',
				component: PlateField,
				normalize() {
					return item.plate_number
				},
			},
			{
				title: 'id',
				field: 'id',
				type: 'hidden',
			},
		],
		containerFields: (item) => [
			{
				title: 'کد کانتینر',
				field: 'container_code_edit',
				type: 'text',
				normalize() {
					return item.container_code
				},
			},
			{
				title: 'id',
				field: 'id',
				type: 'hidden',
			},
		],
	}

}
</script>
