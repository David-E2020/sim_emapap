<template>
 <v-card>
        <v-card-title>
            <h3>Mis Asistencias</h3>
        <v-spacer></v-spacer>
        <!-- <v-btn @click="create();" color="primary" dark class="mb-2">Nuevo</v-btn> -->
        </v-card-title>
        <v-card-text>
            <FullCalendar defaultView="dayGridMonth"
            :plugins="calendarPlugins"
            :locale='es'
            :events="attendances" 
            :options='calendarOptions'
            @select="handleSelect"
            @clickDate="handleDateClick"
            @eventClick="eventClick"
            />
        </v-card-text>
        <!-- <edit-biometric :dialog="dialog" :biometric="biometric" @close="close"  @biometric="update"></edit-biometric>
        <sync-biometric :dialog="dialog_sync" :biometric="biometric" @close="close_sync" ></sync-biometric> -->
        <div class="modal inmodal fade" id="modal-render" tabindex="-5" role="dialog"  aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                     <!-- Modal Header -->
                  <div class="modal-header">
                    <h4 class="modal-title">Detalle de la Boleta</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                  </div>
                    <div class="modal-body">
                        <div class="row">
                                    <div class="col-md-4">
                                        <div class="pull-left">
                                            <p for="">Tipo Boleta: </p>
                                            <p for="">Nombre Completo: </p>
                                            <p for="">Autorizado Por: </p>
                                            <p for="">Fecha inicio: </p>
                                            <p for="">Fecha Fin: </p>
                                            <p for="">Hora inicio: </p>
                                            <p for="">Hora Fin: </p>
                                            <p for="">Motivo: </p>
                                            <p for="">Lugar: </p>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <p>{{data_boleta.request_type.name}} -- {{data_boleta.request_type.code}}</p>
                                        <p>{{data_boleta.employee.first_name}} {{data_boleta.employee.second_name}} {{data_boleta.employee.last_name}} {{data_boleta.employee.mother_last_name}}</p>
                                        <p>{{data_boleta.authorized_name}}</p>
                                        <p>{{data_boleta.date}}</p>
                                        <p>{{data_boleta.todate}}</p>
                                        <p>{{data_boleta.hour_in}}</p>
                                        <p>{{data_boleta.hour_out}}</p>
                                        <p>{{data_boleta.reason}}</p>
                                        <p>{{data_boleta.destiny_place}}</p>
                                        <p></p>
                                    </div>
                                </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
    </v-card>
</template>
<script>
import FullCalendar from '@fullcalendar/vue'
import dayGridPlugin from '@fullcalendar/daygrid'
import interactionPlugin from '@fullcalendar/interaction';
import es from '@fullcalendar/core/locales/es';
import { INITIAL_EVENTS, createEventId } from './event-utils'
export default {
    data:()=>({
        calendarPlugins: [ dayGridPlugin, interactionPlugin],
        calendarOptions: {
        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        initialView: 'dayGridMonth',
        initialEvents: INITIAL_EVENTS, // alternatively, use the `events` setting to fetch from a feed
        editable: true,
        selectable: true,
        selectMirror: true,
        dayMaxEvents: true,
        weekends: true,
        //select: this.handleDateSelect,
        //eventClick: this.handleEventClick,
        //eventsSet: this.handleEvents
        /* you can update a remote database when these fire:
        eventAdd:
        eventChange:
        eventRemove:
        */
      },
        attendances:[],
        es:null,
        showModal: false,
        data_boleta:{request_type:{},employee:{}},
    }),
    mounted()
    {
        this.getAttendances();
        this.es = es;
        
    },
    methods:
    {
        getAttendances()
        {
            axios.get('/api/attendance')
                 .then(response=>{

                        let days=[];
                        response.data.attendances.forEach(attendance => {
                            let event = {   title:attendance.title_entry+' '+attendance.type_module_entry,
                                            date: `${attendance.date} ${attendance.attendance_entry!='00:00:00'?attendance.attendance_entry:attendance.entry}`,
                                            backgroundColor: this.getColor(attendance.state_entry),
                                            textColor: attendance.state_entry=='success'?'#FFFFFF':'#000000', 
                                            id:attendance.id_boleta,
                                        }
                            this.attendances.push(event);
                                event = {   title:attendance.title_output+' '+attendance.type_module_output,
                                            date: `${attendance.date} ${attendance.attendance_output!='00:00:00'?attendance.attendance_output:attendance.output}`,
                                            backgroundColor: this.getColor(attendance.state_output),
                                            textColor: attendance.state_output=='success'?'#FFFFFF':'#000000',
                                            id:attendance.id_boleta,
                                            }
                            this.attendances.push(event);
                        });

                 });
        },
        handleSelect(e){
        },
        handleDateClick(e){
        },
        eventClick: function(e) {
            if (e.event.id==0) {

            }else{
                $("#modal-render").modal()
                    axios.get('/api/attendance/'+e.event.id)
                    .then(response=>{
                        this.data_boleta=response.data.data;
                    });
            }
            
        },
        getColor(state)
        {
            let color = '#FFFFFF'
            switch (state) {
                case 'success':
                    color='#4CAF50';
                    break;
                case 'warning':
                    color='#FFEA00';
                    break;
                case 'danger':
                    color='#FF8A80';
                    break;
                case 'comision':
                    color='#ee82ee';
                    break;
                case 'primary':
                    color='#82b6e8';
                    break;
                case 'sin_gose':
                    color='#15c';
                    break;
                case 'licencia':
                    color='#9B938C';
                    break;
                 case 'fucov':
                    color='#B47F4A';
                    break;
                 case 'baja_medica':
                    color='#86FF33';
                    break;
            }
            return color;
        }

    },
    components: {
        FullCalendar // make the <FullCalendar> tag available
    },
}
</script>
<style>
.fc-left{
text-transform: uppercase;
}
</style>
<style lang='scss'>

  @import '~@fullcalendar/core/main.css';
  @import '~@fullcalendar/daygrid/main.css';

</style>
