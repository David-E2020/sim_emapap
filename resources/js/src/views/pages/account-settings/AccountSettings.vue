<template>
  <v-card id="account-setting-card">
    <!-- tabs -->
    <v-tabs v-model="tab" show-arrows>
      <v-tab v-for="tab in tabs" :key="tab.icon">
        <v-icon size="20" class="me-3">
          {{ tab.icon }}
        </v-icon>
        <span>{{ tab.title }}</span>
      </v-tab>
    </v-tabs>

    <!-- tabs item -->
    <v-tabs-items v-model="tab">
      <v-tab-item v-if="accountSettingData">
        <account-settings-account :account-data="accountSettingData.account"></account-settings-account>
      </v-tab-item>
      
      <v-tab-item>
        <account-settings-security></account-settings-security>
      </v-tab-item>

      <v-tab-item v-if="accountSettingData">
        <account-settings-info :information-data="accountSettingData.information"></account-settings-info>
      </v-tab-item>
    </v-tabs-items>
  </v-card>
</template>

<script>
import { mdiAccountOutline, mdiLockOpenOutline, mdiInformationOutline } from '@mdi/js'
import { ref } from '@vue/composition-api'

// demos
import AccountSettingsAccount from './AccountSettingsAccount.vue'
import AccountSettingsSecurity from './AccountSettingsSecurity.vue'
import AccountSettingsInfo from './AccountSettingsInfo.vue'

export default {
  components: {
    AccountSettingsAccount,
    AccountSettingsSecurity,
    AccountSettingsInfo,
  },
  setup() {
    const tab = ref('')

    // tabs
    const tabs = [
      { title: 'Cuenta', icon: mdiAccountOutline },
      { title: 'Security', icon: mdiLockOpenOutline },
      { title: 'Información', icon: mdiInformationOutline },
    ]

    // account settings data

    return {
      tab,
      tabs,
      // accountSettingData,
      icons: {
        mdiAccountOutline,
        mdiLockOpenOutline,
        mdiInformationOutline,
      },
    }
  },

  data: () => ({
    user: null,
    accountSettingData: {
      account: {
        avatarImg: require('@/assets/images/avatars/1.png').default,
        username: '',
        name: '',
        email: '',
        email2: '',
        ci: '',
        status: 'Active',
        company: 'EMAPA',
      },
      information: {
        bio: 'The name’s John Deo. I am a tireless seeker of knowledge, occasional purveyor of wisdom and also, coincidentally, a graphic designer. Algolia helps businesses across industries quickly create relevant 😎, scaLabel 😀, and lightning 😍 fast search and discovery experiences.',
        birthday: 'February 22, 1995',
        address: '',
        phone: '',
        website: '',
        country: 'USA',
        languages: ['English', 'Spanish'],
        sistemas: [],
        gender: 'male',
      },
    },
  }),
  mounted() {
    this.getUser()
  },

  methods: {
    getUser() {
      this.user = JSON.parse(localStorage.getItem('user'))
      this.accountSettingData.account.username = this.user.usr_usuario
      this.accountSettingData.account.name = this.user.name
      // this.accountSettingData.account.name =
      //   this.user.employee.first_name +
      //   ' ' +
      //   this.user.employee.second_name +
      //   ' ' +
      //   this.user.employee.last_name +
      //   ' ' +
      //   this.user.employee.mother_last_name

      this.accountSettingData.account.email = this.user.email
      this.accountSettingData.account.email2 = this.user.email_verified_at
      // this.accountSettingData.account.ci = this.user.employee.identity_card
      // this.accountSettingData.information.address = this.user.employee.address

      // this.accountSettingData.information.birthday = this.user.employee.birth_date
      // this.accountSettingData.information.phone = this.user.employee.cellphone
      // this.accountSettingData.information.gender = this.user.employee.gender

      // this.accountSettingData.information.sistemas = JSON.parse(this.user.usr_access_sistem)
    },
  },
}
</script>
