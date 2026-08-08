<template>

    <v-card>

    <v-tabs
        v-model="tab"
        color="#AED6F1"
        light
        slider-color="#212121"
        >
            <v-tab href="#tab-1" style="background:#bb8080;">
                <!-- <v-icon>people</v-icon> -->
                1 <br> Datos Personales
            </v-tab>
            <v-tab href="#tab-2" style="background:#bb8080;">
                <!-- <v-icon>peoples</v-icon> -->
                2 <br> Datos Parentesco
            </v-tab>
            <v-tab href="#tab-3" style="background:#bb8080;">
                <!-- <v-icon>mdi-phone</v-icon> -->
                3 <br> Datos Referenciales
            </v-tab>
            <v-tab href="#tab-4">
                <!-- <v-icon>mdi-phone</v-icon> -->
                4 <br> Formacion Academica
            </v-tab>
            <v-tab href="#tab-5">
                <!-- <v-icon>mdi-phone</v-icon> -->
                5 <br> Cursos y/o Seminarios
            </v-tab>
            <v-tab href="#tab-6">
                <!-- <v-icon>mdi-phone</v-icon> -->
                6 <br> Idiomas
            </v-tab>
            <v-tab href="#tab-7">
                <!-- <v-icon>mdi-phone</v-icon> -->
                7 <br> Ofimatica y/o Aplicaciones
            </v-tab>
            <v-tab href="#tab-8">
                <!-- <v-icon>mdi-phone</v-icon> -->
                8 <br> Otros
            </v-tab>
            <v-tab href="#tab-9">

                9 <br> Experiencia Laboral
            </v-tab>

            <v-tab-item
                value="tab-1"
            >
                <v-card flat>
                    <v-card-title> Datos Personales <v-btn v-if="employee.user_edit"  @click="edit_pd()" icon>  <v-icon>mdi-pencil</v-icon> </v-btn> </v-card-title>
                    <v-card-text>
                        <label for="">Nombres:</label> {{ full_name }} <br>
                        <label for="">Cédula de Identidad:</label> {{ employee.identity_card }}<br>
                        <label for="">Fecha de Nacimiento:</label> {{ employee.birth_date }}<br>
                        <label for="">Nacionalidad:{{employee.country?employee.country.name:'' }}</label> <br>
                        <!-- <label for="">Nacionalidad:{{employee.country?employee.contry.name:''}}</label> <br> -->
                        <label for="">Estado Civil: {{civil_status}}</label> <br>
                        <label for="">Libreta Militar: {{employee.has_military_card?'Si':'No'}}</label> <br>
                        <label for="">Número de Libreta: {{employee.military_serial_number}}</label> <br>
                        <label for="">Género:{{gender }}</label> <br>
                        <label for="">Certificado de discapacidad: {{ employee.disability?'Si':'No'}}</label> <br>
                        <label for="">Dirección: {{employee.address}}</label> <br>
                        <label for="">Teléfono: {{employee.phone}} </label> <br>
                        <label for="">Email Personal: {{employee.personal_email}}</label> <br>
                        <label for="">Celular: {{employee.cellphone}}</label> <br>
                        <label for="">Celular Institucional: {{employee.corporate_cell}}</label> <br>
                        <label for="">Correo Institucional: {{employee.corporate_email}}</label> <br>
                        <personal-data :dialog="dialog_pd" :employee="employee" @close="close_pd"  @employee="update_pd"></personal-data>
                    </v-card-text>
                </v-card>
            </v-tab-item>
            <v-tab-item
                value="tab-2"
            >
                <family-edit :dialog="dialog_parentesco" :family="family" @close="close_parentesco"  @family="update_parentesco"  ></family-edit>
                <v-card flat>
                <v-card-title> Datos Parentesco
                    <v-btn v-if="employee.user_edit" icon @click="create_parentesco()"> <v-icon>mdi-pencil</v-icon> </v-btn>
                </v-card-title>
                <v-card-text>


                <table class="table">
                    <thead>
                        <tr class="rrhh-primary">
                            <td>Nombre</td>
                            <td>Parentesco</td>
                            <td>Edad</td>
                            <td>Fecha de Nacimiento</td>
                            <td>Teléfono</td>
                            <td>Celular</td>
                            <td>Referencia de Emergencia</td>
                            <td></td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(family,index) in employee.families" :key="index" >
                            <!-- <td>{{family.first_name || ''+' '+family.second_name+' '+family.last_name+' '+family.mother_last_name}}</td> -->
                            <td>{{fullName(family) }}</td>
                            <td>{{ family.kinship?family.kinship.name:'' }}</td>
                            <td>{{family.age}}</td>
                            <td>{{family.birth_date}}</td>
                            <td>{{family.phone}}</td>
                            <td>{{family.cellphone}}</td>
                            <td>
                                <!-- {{family.is_reference?'Si':'No'}} -->
                                <v-chip
                                    class="ma-2"
                                    color="cyan"
                                    text-color="white"
                                    v-if='family.is_reference'
                                >

                                    {{family.is_reference?'Si':'No'}}
                                </v-chip>
                            </td>
                            <td>
                                <v-btn v-if="employee.user_edit" icon  @click="edit_parentesco(family)"> <v-icon >mdi-pencil</v-icon> </v-btn>
                                <v-btn v-if="employee.user_edit" icon  @click="delete_parentesco(index)"> <v-icon >mdi-delete</v-icon> </v-btn>
                            </td>
                        </tr>
                    </tbody>
                </table>


                </v-card-text>
                </v-card>
            </v-tab-item>
            <v-tab-item
                value="tab-3"
            >
                <reference-edit :dialog="dialog_reference" :employee="employee" @close="close_reference"  @employee="update_reference"></reference-edit>
                <v-card flat>
                    <v-card-title> Datos Referenciales <v-btn v-if="employee.user_edit" icon @click="edit_reference()">  <v-icon>mdi-pencil</v-icon> </v-btn></v-card-title>
                    <v-card-text>
                        <label for="">AFP:</label> {{employee.contribution?employee.contribution.afp_name:''}} <br>
                        <label for="">NUA/CUA:</label> {{employee.cua_nua}}<br>
                        <label for="">Banco:</label> {{employee.bank}} <br>
                        <label for="">Nro Cuenta:</label> {{employee.account_number}}<br>
                        <label for="">Seguro a Corto Plazo:</label> {{employee.health_box?employee.health_box.name:''}}<br>
                        <label for="">Matrícula:</label> {{employee.registration_number_medical}}<br>
                        <label for="">Tipo de Sangre:</label> {{employee.blood_type}}<br>
                        <label for="">Doctor/Médico:</label> {{employee.doctor_name}} <br>
                        <label for="">Numero de Dependencia:</label> {{employee.number_dependency}} <br>
                        <!-- <label for="">Declaracion Jurada:</label> {{employee.sworn_declaration?'Si':'No'}} <br>
                        <label for="">Fecha de Declaracion:</label> {{employee.date_sworn_declaration}} <br>
                        <label for="">Fecha de Recepcion en Recursos Humanos:</label> {{employee.date_reception}} <br>
                        <label for="">Numero de Declaracion:</label> {{employee.number_declaration}} <br> -->
                    </v-card-text>
                </v-card>


            </v-tab-item>

            <v-tab-item
                value="tab-4"
            >
                <academic-edit :dialog="dialog_academic" :academic="academic" @close="close_academic"  @academic="update_academic"  ></academic-edit>
                <v-card flat>
                <v-card-title> Formación Académica
                    <v-btn v-if="employee.user_edit" icon @click="create_academic()"> <v-icon>mdi-account-plus</v-icon> </v-btn>
                </v-card-title>
                <v-card-text>


                <table class="table">
                    <thead>
                        <tr  class="rrhh-primary">
                            <td>Formación Académica</td>
                            <td>Documentos</td>
                            <td>Estado</td>
                            <td>Institución</td>
                            <td>Grado</td>
                            <td>Título</td>
                            <td>Fecha de Emisión</td>
                            <td>Documento</td>
                            <td></td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(academic,index) in employee.academic_trainings" :key="index" >

                            <td>{{academic.name}}</td>
                            <td>{{academic.document}}</td>
                            <td>{{academic.state}}</td>
                            <td>{{academic.instituion}}</td>
                            <td>{{academic.grade}}</td>
                            <td>{{academic.has_title?'Si':'No'}}</td>
                            <td>{{academic.date}}</td>
                            <td>
                                <v-icon v-if="academic.file_path"  @click="showDialog(`academic_trainings/${academic.file_path}`)" >insert_drive_file</v-icon>
                            </td>
                            <td>
                                <v-layout justify-space-around>
                                    <v-btn v-if="employee.user_edit" icon  @click="edit_academic(academic)"> <v-icon >mdi-pencil</v-icon> </v-btn>
                                    <v-btn v-if="employee.user_edit" icon  @click="delete_academic(index)"> <v-icon >mdi-delete</v-icon> </v-btn>
                                </v-layout>
                            </td>
                        </tr>
                    </tbody>
                </table>


                </v-card-text>
                </v-card>
            </v-tab-item>
            <v-tab-item
                value="tab-5"
            >
                <course-edit :dialog="dialog_course" :course="course" @close="close_course"  @course="update_course"  ></course-edit>
                <v-card flat>
                <v-card-title> Cursos y Seminarios
                    <v-btn v-if="employee.user_edit" icon @click="create_course()"> <v-icon>mdi-account-plus</v-icon> </v-btn>
                </v-card-title>
                <v-card-text>


                <table class="table">
                    <thead>
                        <tr  class="rrhh-primary">
                            <td>Gestión</td>
                            <td>Institución</td>
                            <td>Nombre</td>
                            <td>Duración</td>
                            <td>Documento</td>
                            <td>Visualizacion</td>
                            <td></td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(course,index) in employee.courses" :key="index" >

                            <td>{{course.date}}</td>
                            <td>{{course.name}}</td>
                            <td>{{course.institution}}</td>
                            <td>{{course.hours}}</td>
                            <td>
                                <v-icon v-if="course.file_path"  @click="showDialog(`courses/${course.file_path}`)" >insert_drive_file</v-icon>
                            </td>
                            <td> 
                                <v-btn v-if="employee.user_edit" icon  @click="edit_course(course)"> <v-icon >mdi-pencil</v-icon> </v-btn> 
                                <v-btn v-if="employee.user_edit" icon  @click="delete_course(index)"> <v-icon >mdi-delete</v-icon> </v-btn> 
                            </td>
                        </tr>
                    </tbody>
                </table>


                </v-card-text>
                </v-card>
            </v-tab-item>
            <v-tab-item
                value="tab-6"
            >
                <language-edit :dialog="dialog_language" :language="language" @close="close_language"  @language="update_language"  ></language-edit>
                <v-card flat>
                <v-card-title> Idiomas
                    <v-btn v-if="employee.user_edit" icon @click="create_language()"> <v-icon>mdi-account-plus</v-icon> </v-btn>
                </v-card-title>
                <v-card-text>


                <table class="table">
                    <thead>
                        <tr  class="rrhh-primary">
                            <td>Gestión</td>
                            <td>Institución</td>
                            <td>Idioma</td>
                            <td>Documento</td>
                            <td></td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(language,index) in employee.languages" :key="index" >

                            <td>{{language.date}}</td>
                            <td>{{language.institution}}</td>
                            <td>{{language.name}}</td>
                            <td>
                                <v-icon v-if="language.file_path"  @click="showDialog(`/language/${language.file_path}`)" >insert_drive_file</v-icon>
                            </td>
                            <td> 
                                <v-btn v-if="employee.user_edit" icon  @click="edit_language(language,index)"> <v-icon >mdi-pencil</v-icon> </v-btn> 
                                <v-btn v-if="employee.user_edit" icon  @click="delete_language(index)"> <v-icon >mdi-delete</v-icon> </v-btn> 
                            </td>
                        </tr>
                    </tbody>
                </table>


                </v-card-text>
                </v-card>
            </v-tab-item>
            <v-tab-item
                value="tab-7"
            >
                <package-edit :dialog="dialog_package" :paquete="paquete" @close="close_package"  @package="update_package"  ></package-edit>
                <v-card flat>
                <v-card-title> Ofimatica y/o Aplicaciones
                    <v-btn icon @click="create_package()" v-if="employee.user_edit"> <v-icon>mdi-account-plus</v-icon> </v-btn>
                </v-card-title>
                <v-card-text>


                <table class="table">
                    <thead>
                        <tr  class="rrhh-primary">
                            <td>Gestión</td>
                            <td>Institución</td>
                            <td>Paquetes</td>
                            <td>Documento</td>
                            <td></td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(paquete,index) in employee.packages" :key="index" >

                            <td>{{paquete.date}}</td>
                            <td>{{paquete.institution}}</td>
                            <td>{{paquete.name}}</td>
                            <td>
                                <v-icon v-if="paquete.file_path"  @click="showDialog(`packages/${paquete.file_path}`)" >insert_drive_file</v-icon>
                            </td>
                            <td> 
                                <v-btn v-if="employee.user_edit" icon  @click="edit_package(paquete)"> <v-icon >mdi-pencil</v-icon> </v-btn> 
                                <v-btn v-if="employee.user_edit" icon  @click="delete_package(index)"> <v-icon >mdi-delete</v-icon> </v-btn>
                            </td>
                        </tr>
                    </tbody>
                </table>


                </v-card-text>
                </v-card>
            </v-tab-item>
            <v-tab-item
                value="tab-8"
            >
                <size-edit :dialog="dialog_size" :employee="employee" @close="close_size"  @employee="update_zise"></size-edit>
                <v-card flat>
                    <v-card-title> Espeficicación de Tallas <v-btn v-if="employee.user_edit" icon @click="dialog_size=true">  <v-icon>mdi-pencil</v-icon> </v-btn></v-card-title>
                    <v-card-text>
                        <label for="" v-if="employee.gender == 'F'" >Talla de Blusas:</label> {{employee.blouses}} <br>
                        <label for="">Talla de Camisas:</label> {{employee.shirt}}<br>
                        <label for="">Talla de Poleras:</label> {{employee.t_shirt}} <br>
                        <label for="">Talla de Chamarras:</label> {{employee.jacket}}<br>
                        <label for="">Nro de Bota:</label> {{employee.boots_number}}<br>

                        <!-- <v-btn color="success" v-if="employee.user_edit" @click="save_employee()"> Registrar Información </v-btn> -->
                    </v-card-text>
                </v-card>


            </v-tab-item>
            <v-tab-item
                value="tab-9"
            >
                <!-- <size-edit :dialog="dialog_size" :employee="employee" @close="close_size"  @employee="update_zise"></size-edit> -->
                <work-edit :dialog="dialog_work" :work="work" @close="close_work"  @work="update_work"  ></work-edit>
                <v-card flat>
                    <v-card-title> Experiencia Laboral
                        <v-btn icon @click="create_work()" v-if="employee.user_edit"> <v-icon>mdi-account-plus</v-icon> </v-btn>
                    </v-card-title>
                    <v-card-text>
                        <table class="table">
                            <thead>
                                <tr  class="rrhh-primary">
                                    <td>Mes Inicio</td>
                                    <td>Año Inicio</td>
                                    <td>Mes Fin</td>
                                    <td>Año Fin</td>
                                    <td>Empresa</td>
                                    <td>Cargo</td>
                                    <td>Telefono</td>
                                    <td></td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(work,index) in employee.works" :key="index" >

                                    <td>{{work.mes_inicio}}</td>
                                    <td>{{work.anio_inicio}}</td>
                                    <td>{{work.mes_fin}}</td>
                                    <td>{{work.anio_fin}}</td>
                                    <td>{{work.institution}}</td>
                                    <td>{{work.position}}</td>
                                    <td>{{work.phone}}</td>
                                    <td> 
                                        <v-btn v-if="employee.user_edit" icon  @click="edit_work(work,index)"> <v-icon >mdi-pencil</v-icon> </v-btn> 
                                        <v-btn v-if="employee.user_edit" icon  @click="delete_work(index)"> <v-icon >mdi-delete</v-icon> </v-btn> 
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <v-btn color="success" v-if="employee.user_edit" @click="save_employee()"> Registrar Información </v-btn>
                    </v-card-text>
                </v-card>


            </v-tab-item>


        </v-tabs>

        <div class="text-xs-center mt-3" v-if="employee">
        <!-- {{url}} -->
        <v-btn @click="next">Siguiente</v-btn>

        <v-btn  v-if="!employee.user_edit" @click="showDialog(`/api/ficha_personal/${employee.id}`)"   >  Ver Reporte <v-icon right dark >mdi-printer</v-icon> </v-btn>
       
        </div>
        <v-dialog
        v-model="dialog_report"
        width="1000"
        >
        <v-card>
            <!-- <v-card-title
            class="headline grey lighten-2"
            primary-title
            >
            Reporte
            </v-card-title> -->

            <v-card-text>
                <iframe id='ireport' :src="url" frameborder="0" allowtransparency="true" style="width:100%;height:500px"></iframe>

            </v-card-text>

            <v-divider></v-divider>

            <v-card-actions>
            <div class="flex-grow-1"></div>
            <v-btn
                color="primary"
                text
                @click="dialog_report = false"
            >
                Cerrar
            </v-btn>
            </v-card-actions>
        </v-card>
        </v-dialog>



    </v-card>



</template>
<script>
import PersonalData from './PersonalData.vue';
import FamilyEdit from './FamilyEdit.vue';
import ReferenceEdit from './ReferenceEdit.vue';
import AcademicEdit from './AcademicEdit.vue';
import CourseEdit from './CourseEdit.vue';
import LanguageEdit from './LanguageEdit.vue';
import PackageEdit from './PackageEdit.vue';
import SizeEdit from './SizeEdit.vue';
import WorkEdit from './WorkExperienceEdit.vue';
export default
{

    data:()=>({
        employee:{},
        text:'',
        tab: null,
        dialog_pd:false, //dialog personal data
        dialog_parentesco:false,
        dialog_reference:false,
        dialog_academic:false,
        dialog_course:false,
        dialog_language:false,
        dialog_package:false,
        dialog_report:false,
        dialog_size:false,
        dialog_work:false,
        family:{},
        academic:{},
        course:{},
        language:{},
        paquete:{},
        work:{},
        civil_statuses:[{id:'C',name:'Casado(a)'},{id:'S',name:'Soltero(a)'},{id:'V',name:'Viudo(a)'},{id:'D',name:'Divorciado(a)'},{id:'U',name:'Concubinato'}],
        countries: [],
        url:''

    }),
    mounted()
    {
        axios.get('/api/employee_info')
            .then(response => {
                this.employee = response.data.employee;
                // this.employee.courses.push({date:'2019-01-01',name:'Ley 1178',institution:'EGGP',hours:10});
                // this.employee.courses.push({date:'2019-01-01',name:'Responsibilidad Funcionaria',institution:'EGGP',hours:10});
                // this.employee.courses.push({date:'2019-01-01',name:'Politicas Publicas',institution:'EGGP',hours:10});
                // this.employee.languages.push({date:'2019-01-01',name:'Aymara',institution:'EGGP'});
            })
            .catch(error => {
            });
    },
    methods:{
        next ()
        {
            let number = this.tab.toString().substr(4);
            number++;
            if(number>9){
                number =1;
            }
            this.tab = 'tab-'+number;
            // const tab = parseInt(this.tab)
            // this.tab = (tab < 2 ? tab + 1 : 0)
        },
        showDialog(url){
            var data_url=url.split('/');
            var cadena="/"+data_url[1]+"/"+data_url[2];
            if (cadena=='/api/ficha_personal') {
            this.url =url;
            // document.getElementById('ireport').contentWindow.location.reload();
            this.dialog_report = true; 
            }else{
               this.url ='/storage/'+url;
                // document.getElementById('ireport').contentWindow.location.reload();
                this.dialog_report = true; 
            }
        },
        edit_pd () {
            // this.editedIndex = this.employees.indexOf(item)
            // axios.get(`/api/employee/${item.id}/edit`)
            // .then(response => {
            //     this.employee = response.data.employee
            // })
            // .catch(error => {
            // });

            this.dialog_pd = true
        },
        close_pd() {
            this.dialog_pd = false;
        },
        update_pd (item) {
            this.dialog_pd = false;
        },
        create_parentesco(){
            this.family = {};
            this.dialog_parentesco = true;
        },
        edit_parentesco(item){
            this.family = item;
            this.dialog_parentesco = true;
            // this.employee.families.push*
        },
        delete_parentesco(index)
        {
            this.employee.families.splice(index, 1)
        },
        close_parentesco(){
            this.dialog_parentesco = false;
        },
        update_parentesco(item)
        {
            let index = this.employee.families.indexOf(item)
            if (index > -1)
            {
                Object.assign(this.employee.families[index], item)

            } else {
                // this.desserts.push(this.editedItem)
                this.employee.families.push(item);
            }
            this.GuardarFamiliar(this.employee);
            this.dialog_parentesco = false;
        },

        GuardarFamiliar(data){
            axios.post('/api/actualizar_empleado',data)
                .then((response)=>{
                    iziToast.success({
                        position: 'topRight',
                        title: 'Datos del parentesco',
                        message: 'Se actualizado los datos del parentesco'
                    });
                    this.$emit('employee',response.data.employee)
                }).catch((error)=> {
                    iziToast.error({
                        position: 'topRight',
                        title: 'Ingrese los datos faltantes',
                        message: 'No se pudo realizar el registro faltan campos'
                    });
                });
        },



        close_reference(){
            this.dialog_reference = false;
        },
        update_reference(item)
        {
            this.dialog_reference = false;
        },
        edit_reference()
        {
            this.dialog_reference = true;
        },
        edit_academic(item)
        {
            axios.get(`api/academic_training/${item.id}/edit`)
                .then(response=>{
                    this.academic = response.data.academic_training;
                    this.dialog_academic = true;
                });

        },
        create_academic()
        {
            this.academic = {employee_id:this.employee.id};
            this.dialog_academic = true;
        },
        update_academic(item)
        {
            let formData = new FormData();
            if(item.id)
            {
                formData.set('id',item.id);
            }
            formData.set('employee_id',item.employee_id);
            formData.set('date',item.date);
            formData.set('document',item.document);
            formData.set('employee_id',item.employee_id);
            formData.set('grade',item.grade);
            formData.set('has_title',item.has_title||false);
            formData.set('instituion',item.instituion);
            formData.set('name',item.name);
            formData.set('state',item.state);
            formData.append("file", item.curriculum_file || '');
            axios.post('api/academic_training', formData,{
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
                })
                .then(response=>{
                     iziToast.success({
                        position: 'topRight',
                        title: 'Formacion academica registrada correctamente',
                        message: 'REGISTRADO!'
                    });
                    // let index = this.employee.academic_trainings.indexOf(response.data.academic_training);
                    let index = this.employee.academic_trainings.findIndex(x => x.id ===response.data.academic_training.id);
                    if(index>-1)
                    {
                        Object.assign(this.employee.academic_trainings[index], response.data.academic_training);
                    }else{
                        this.employee.academic_trainings.push(response.data.academic_training);
                    }
                    this.dialog_academic = false;
                });

            //adicionar
        },
        close_academic(){
            this.dialog_academic = false;
        },
        delete_academic(index)
        {
            // let index = this.employee.academic_trainings.findIndex(x => x.id ===this.academic.id);
            axios.delete(`/api/academic_training/${this.employee.academic_trainings[index].id}`)
            .then((response)=>{
                this.employee.academic_trainings.splice(index, 1)

                iziToast.success({
                    title: 'Eliminacion Correcta',
                    message: 'Se elimino '+response.data.name,
                });
            })
            .catch( (error)=> {
                iziToast.error({
                    title: 'Error',
                    message: 'Contactese con el Administrador de la Pagina: '+error,
                });
            });
        },
        create_course()
        {
            this.course =  {employee_id:this.employee.id};
            this.dialog_course = true;
        },
        edit_course(item){
            // this.course = _.cloneDeep(item);
            // // this.course.index = index;
            // this.dialog_course = true;
             axios.get(`api/course/${item.id}/edit`)
                .then(response=>{
                    this.course = response.data.course;
                    this.dialog_course = true;
                });
        },
        update_course(item){
          
            let formData = new FormData();
            if(item.hasOwnProperty('id'))
            {
                formData.set('id',item.id);
            }
            formData.set('employee_id',item.employee_id);
            formData.set('date',item.date);
            formData.set('institution',item.institution);
            formData.set('name',item.name);
            formData.set('hours',item.hours);
            formData.append("file", item.curriculum_file || '');
            axios.post('api/course', formData,{
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                })
                .then(response=>{
                      iziToast.success({
                            position: 'topRight',
                            title: "CURSO REGISTRADO CORRECTAMENTE",
                            message: 'REGISTRADO!',
                            theme: 'question', // dark
                        });
                    // let index = this.employee.academic_trainings.indexOf(response.data.academic_training);
                    let index = this.employee.courses.findIndex(x => x.id ===response.data.course.id);
                    if(index>-1)
                    {
                        Object.assign(this.employee.courses[index], response.data.course);
                    }else{
                        this.employee.courses.push(response.data.course);
                    }
                    this.dialog_course = false;
                });

            // if(item.hasOwnProperty('index'))
            // {
            //     Object.assign(this.employee.courses[item.index], item);
            // }else{
            //     this.employee.courses.push(item);
            // }
         
            //adicionar
        },
        close_course(){
            this.dialog_course = false;
        },
        delete_course(index)
        {
            // this.employee.courses.splice(index, 1)
            axios.delete(`/api/course/${this.employee.courses[index].id}`)
            .then((response)=>{
                this.employee.courses.splice(index, 1)

                iziToast.success({
                    title: 'Eliminacion Correcta',
                    message: 'Se elimino '+response.data.name,
                });
            })
            .catch( (error)=> {
                iziToast.error({
                    title: 'Error',
                    message: 'Contactese con el Administrador de la Pagina: '+error,
                });
            });
        },
        create_language()
        {
            this.language = {employee_id:this.employee.id};
            this.dialog_language = true;
        },
        edit_language(item,index)
        {
            this.dialog_language =true;
             axios.get(`api/language/${item.id}/edit`)
                .then(response=>{
                    this.language = response.data.language;
                    this.dialog_language = true;
            });
        },
        update_language(item){
            let formData = new FormData();
            if(item.hasOwnProperty('id'))
            {
                formData.set('id',item.id);
            }
            formData.set('employee_id',item.employee_id);
            formData.set('date',item.date);
            formData.set('institution',item.institution);
            formData.set('name',item.name);
            formData.append("file", item.curriculum_file || '');
            axios.post('api/language', formData,{
                            headers: {
                                    'Content-Type': 'multipart/form-data'
                                    }
                        })
                .then(response=>{
                   iziToast.success({
                        title: 'El idioma se guardo correctamente',
                        message: 'Idioma guardado en base de datos',
                     });
                    let index = this.employee.languages.findIndex(x => x.id ===response.data.language.id);
                    if(index>-1)
                    {
                        Object.assign(this.employee.languages[index], response.data.language);
                    }else{
                        this.employee.languages.push(response.data.language);
                    }
                    this.dialog_language = false;
                });



            //adicionar
        },
        close_language(){
            this.dialog_language = false;
        },
        delete_language(index)
        {
            axios.delete(`/api/language/${this.employee.languages[index].id}`)
            .then((response)=>{
                this.employee.languages.splice(index, 1)

                iziToast.success({
                    title: 'Eliminacion Correcta',
                    message: 'Se elimino '+response.data.name,
                });
            })
            .catch( (error)=> {
                iziToast.error({
                    title: 'Error',
                    message: 'Contactese con el Administrador de la Pagina: '+error,
                });
            });
        },
        create_package()
        {
            this.paquete = {employee_id:this.employee.id};
            this.dialog_package = true;
        },
        edit_package(item)
        {
             axios.get(`api/package/${item.id}/edit`)
                .then(response=>{
                    this.paquete = response.data.package;
                    this.dialog_package = true;
                });
        },
        update_package(item){
            let formData = new FormData();
            if(item.hasOwnProperty('id'))
            {
                formData.set('id',item.id);
            }
            formData.set('employee_id',item.employee_id);
            formData.set('date',item.date);
            formData.set('institution',item.institution);
            formData.set('name',item.name);
            formData.append("file", item.curriculum_file || '');
            axios.post('api/package', formData,{
                            headers: {
                                    'Content-Type': 'multipart/form-data'
                                    }
                        })
                .then(response=>{
                    // let index = this.employee.academic_trainings.indexOf(response.data.academic_training);
                    let index = this.employee.packages.findIndex(x => x.id ===response.data.package.id);
                    if(index>-1)
                    {
                        Object.assign(this.employee.packages[index], response.data.package);
                    }else{
                        this.employee.packages.push(response.data.package);
                    }
                    this.dialog_package = false;
                });
            //adicionar
        },
        close_package(){
            this.dialog_package = false;
        },
        delete_package(index)
        {
            axios.delete(`/api/package/${this.employee.packages[index].id}`)
            .then((response)=>{
                this.employee.packages.splice(index, 1)

                iziToast.success({
                    title: 'Eliminacion Correcta',
                    message: 'Se elimino '+response.data.name,
                });
            })
            .catch( (error)=> {
                iziToast.error({
                    title: 'Error',
                    message: 'Contactese con el Administrador de la Pagina: '+error,
                });
            });
        },
        create_work()
        {
            this.work = {employee_id:this.employee.id};
            this.dialog_work = true;
        },
        edit_work(item,index){
            this.work = _.cloneDeep(item);
            this.work.index = index;
            this.dialog_work = true;
        },
        update_work(item)
        {
                let formData = new FormData();
                if(item.hasOwnProperty('id'))
                {
                    formData.set('id',item.id);
                }
                formData.set('employee_id',item.employee_id);
                formData.set('mes_inicio',item.mes_inicio);
                formData.set('anio_inicio',item.anio_inicio);
                formData.set('mes_fin',item.mes_fin);
                formData.set('anio_fin',item.anio_fin);
                formData.set('phone',item.phone);
                formData.set('institution',item.institution);
                formData.set('position',item.position);
                axios.post('api/store_work_experience', formData,{
                        headers: {
                                'Content-Type': 'multipart/form-data'
                                }
                    })
                    .then(response=>{
                        iziToast.success({
                            title: 'La Experiencia laboral se guardo correctamente',
                            message: 'La Experiencia guardado en base de datos',
                        });
                        // let index = this.employee.academic_trainings.indexOf(response.data.academic_training);
                        let index = this.employee.works.findIndex(x => x.id ===response.data.package.id);
                        if(index>-1)
                        {
                            Object.assign(this.employee.works[index], response.data.package);
                        }else{
                        this.employee.works.push(response.data.package);
                        }
                        this.dialog_package = false;
                });
            this.dialog_work = false;
            //adicionar
        },
        close_work(){
            this.dialog_work = false;
        },
        delete_work(index)
        {
            //this.employee.works.splice(index, 1) //verificar existencia de array
            axios.delete(`/api/work_delete/${this.employee.works[index].id}`)
            .then((response)=>{
                this.employee.works.splice(index, 1)

                iziToast.success({
                    title: 'Eliminacion Correcta',
                    message: 'Se elimino '+response.data.name,
                });
            })
            .catch( (error)=> {
                iziToast.error({
                    title: 'Error',
                    message: 'Contactese con el Administrador de la Pagina: '+error,
                });
            });
        },
        close_size()
        {
            this.dialog_size = false;
        },

        update_zise(item){
            // this.employee.packages.push(item);
            this.dialog_size = false;

            axios.post('/api/actualizar_empleado',item)
                .then((response)=>{
                    iziToast.success({
                        position: 'topRight',
                        title: 'Datos actualizados',
                        message: 'Se actualizado los datos'
                    });
                    this.$emit('employee',response.data.employee)
                }).catch((error)=> {
                    iziToast.error({
                        position: 'topRight',
                        title: 'Ingrese los datos faltantes',
                        message: 'No se pudo realizar el registro faltan campos'
                    });
                });
            //adicionar
        },
        save_employee()
        {
            this.$swal({
             title: '¿Esta seguro de registrar los datos de su persona?',
              text: 'Verifique todos sus datos personales antes de realizar el registro se bloqueara los botones de edición',
              type: 'warning',
              showCancelButton: true,
              confirmButtonText: 'Si, Registrar!',
              cancelButtonText: 'No, cancelar',
              showCloseButton: true,
              showLoaderOnConfirm: true
              }).then((result) => {
              if(result.value) {
                 axios.post('/api/save_employee',this.employee)
                .then(response=>{
                    iziToast.success({
                        position: 'topRight',
                        title: 'Se registro sus datos exitosamente',
                        message: 'No se volvera a habilitar los datos para edicion debe llamar a recusos humanos para su edición'
                    });
                    this.employee = response.data.employee;
                    this.url=`/api/ficha_personal/${this.employee.id}`
                    document.getElementById('ireport').contentWindow.location.reload();
                    this.dialog_report = true;
                });
              } else {
                this.$swal('Cancelado', 'No se registro sus datos', 'info')
              }
            })

            //save academic training

        },
        fullName(item){
            let first_name = item.first_name || '';
            let second_name = item.second_name || '';
            let last_name = item.last_name || '';
            let mother_last_name = item.mother_last_name || '';
            return first_name + ' '+ second_name + ' '+ last_name + ' ' + mother_last_name;
        },

    },
    computed:{
        full_name(){
            let full_name = '';
            if(this.employee){
                full_name= this.employee.first_name+' '+this.employee.second_name+' '+this.employee.last_name+' '+this.employee.mother_last_name ;
            }
            return full_name;
        },
        civil_status(){
            let name=''
            if(this.employee.civil_status)
            {
                let obj = _.find(this.civil_statuses, (o)=> { return o.id ==this.employee.civil_status });
                if(obj){
                    name = obj.name
                }
            }
            return name;
        },
        gender(){
            let name=''
            if(this.employee.gender)
            {
                if(this.employee.gender=='M')
                {
                    name = 'Masculino'
                }else{
                    name = 'Femenino'
                }
            }
            return name;
        },
        getUrl()
        {
            return `/api/ficha_personal/${this.employee.id}`
        }


    },
    components: {
        PersonalData,
        FamilyEdit,
        ReferenceEdit,
        AcademicEdit,
        CourseEdit,
        LanguageEdit,
        PackageEdit,
        SizeEdit,
        WorkEdit

    }
}
</script>
