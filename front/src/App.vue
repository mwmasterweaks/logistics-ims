<template>
  <div class="theme-black" id="app">
    <my-login v-if="!isAuth"></my-login>
    <!-- <my-maintenance v-if="!isAuth"></my-maintenance> -->
    <div v-if="isAuth">
      <my-navbar></my-navbar>
      <my-sidebar></my-sidebar>

      <section class="content">
        <router-view></router-view>
      </section>
    </div>
  </div>
</template>

<script>
import Login from "./components/Login.vue";
import Navbar from "./components/Navbar.vue";
import Sidebar from "./components/Sidebar.vue";
import Maintenance from "./components/Maintenance.vue";

export default {
  components: {
    "my-login": Login,
    "my-navbar": Navbar,
    "my-sidebar": Sidebar,
    "my-maintenance": Maintenance
  },

  data() {
    return {
      isAuth: null
    };
  },

  created() {
    this.isAuth = this.$auth.isAuthenticated();
  }
};
</script>

<style>
@import url(./plugins/bootstrap/css/bootstrap.css);
@import url(./plugins/node-waves/waves.css);
@import url(./plugins/animate-css/animate.css);
@import url(./plugins/jquery-datatable/skin/bootstrap/css/dataTables.bootstrap.css);
@import url(./css/style.css);
@import url(./css/themes/all-themes.css);
</style>
