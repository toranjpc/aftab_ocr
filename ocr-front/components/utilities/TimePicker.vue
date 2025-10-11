<template>
  <div>
    <v-text-field :rules="pickerRules"  v-model="datetime" label="تاریخ شروع امتحان" @click.stop="mdilog = true"></v-text-field>
    <v-dialog
      v-model="mdilog"
      max-width="350"
      class="white"
    >
      <v-card>
        <v-row class="ma-0">
          <v-bottom-navigation
            :value="setbtn"
            color="purple lighten-1"
          >
            <v-btn    @click.stop="calender =true, clock=false" class="col-6">
              <span>تاریخ</span>
              {{start_date}}
              <i class="fad fa-calendar-edit fa-2x"></i>
            </v-btn>

            <v-btn @click.stop="calender =false, clock=true" class="col-6">
              <span>ساعت</span>
              <i class="fad fa-alarm-clock fa-2x"></i>
            </v-btn>
          </v-bottom-navigation>
        </v-row>
        <v-row v-show="calender" justify="center" class="ma-0">
          <v-date-picker :value="start_date" :min="min" width="100%" v-model="picker"></v-date-picker>
        </v-row>
        <v-row v-show="clock" justify="center" class="ma-0">
          <v-time-picker :min="minclock" class="col-12 pa-0" v-model="e7" format="24hr"></v-time-picker>
        </v-row>
        <v-divider></v-divider>
        <v-card-actions>
          <v-spacer></v-spacer>

          <v-btn :disabled="!(e7&&picker)" @click.stop="setin" color="success" text>ثبت</v-btn>
          <v-btn @click.stop="mdilog = false" color="error" text>بستن</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>

  export default {
    name: "timePicker",
    props:['def'],
    mounted() {
      this.setDef()
    },
    data(){
      return{
        min: new Date().toISOString().substr(0, 10),
        minclock: new Date().toTimeString(),
        setbtn:0,
        e7: null,
        calender:true,
        clock:false,
        picker: null,
        mdilog: false,
        datetime:  null,
        pickerRules: [
          v => !!v || 'الزامی است',
        ],
      }
    },
    methods:{
      setin(){
        var fields= this.e7.split(":")
        var h= fields[0]
        var m= fields[1]
        var send
        var test = new Date(this.picker).setHours(parseInt(h), parseInt(m))
        this.datetime = new Date(test).toLocaleString('fa-IR')
        var options = { hour12: false }
        send  = new Date(test).toLocaleString()
        this.$emit('startTime',send)
        this.mdilog =false
      },
      setDef(){
        this.datetime =  new Date(this.def).toLocaleString('fa-IR')
      },
    },
    watch:{
      picker: function (newValue, oldValue) {
        if(newValue != oldValue){
          if(newValue != this.min){
            this.minclock =null
          }
          else {
            this.minclock = new Date().toTimeString()
          }
          this.clock =true;
          this.calender = false;
          this.setbtn=1;
        }
      }
    },

    computed:{
      btnSet(){
        if(this.clock){
          this.setbtn = 1
        }
        else
          this.setbtn=0
      }
    }
  }
</script>

<style scoped>

</style>
