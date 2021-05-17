<template>
  <div class="container-fluid">
    <pre-loader></pre-loader>
    <div class="block-header">
      <button type="button" class="btn btn-default waves-effect" @click="exit">
        <i class="material-icons">keyboard_backspace</i>
        <span>Back</span>
      </button>
    </div>
    <div class="row clearfix">
      <div class="col-lg-6 col-md-6">
        <div class="card">
          <div class="header">
            <h2>Create New Client</h2>
          </div>
          <div class="body">
            <div class="row clearfix">
              <div class="col-md-12">
                <br />
                <span>Name</span>
                <div class="input-group">
                  <div class="form-line">
                    <input
                      type="text"
                      ref="name"
                      name="name"
                      class="form-control"
                      v-validate="'required'"
                      v-model.trim="client.name"
                      autocomplete="off"
                      autofocus="on"
                    />
                  </div>
                  <small
                    class="text-danger pull-left"
                    v-show="errors.has('name')"
                    >{{ errors.first("name") }}</small
                  >
                </div>
              </div>
            </div>

            <div class="row clearfix">
              <div class="col-md-12">
                <span>Contact</span>
                <div class="input-group">
                  <div class="form-line">
                    <input
                      type="text"
                      name="contact"
                      class="form-control"
                      v-model.trim="client.contact"
                      autocomplete="off"
                    />
                  </div>
                </div>
              </div>
            </div>

            <div class="row clearfix">
              <div class="col-md-12">
                <span>Address</span>
                <div class="input-group">
                  <div class="form-line">
                    <textarea
                      type="text"
                      name="address"
                      class="form-control"
                      v-model.trim="client.address"
                      rows="5"
                    ></textarea>
                  </div>
                </div>
              </div>
            </div>

            <div class="row clearfix">
              <div class="col-md-12">
                <br />
                <span>Client type</span>
                <div class="input-group">
                  <div class="form-line">
                    <input
                      type="text"
                      name="client_type"
                      class="form-control"
                      v-model.trim="client.client_type"
                      autocomplete="off"
                    />
                  </div>
                </div>
              </div>
            </div>

            <div class="row clearfix">
              <div class="col-md-6 col-md-offset-6">
                <button
                  class="btn btn-lg btn-info waves-effect waves-light pull-right"
                  @click="create"
                  :disabled="!roles.create_client"
                >
                  Create
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import swal from "sweetalert";

export default {
  data() {
    return {
      client: {
        name: "",
        contact: "",
        address: "",
        client_type: "",
      },
      roles: [],
    };
  },

  created() {
    this.roles = this.$global.getRoles();
  },

  methods: {
    create() {
      this.$validator.validateAll().then((result) => {
        if (result) {
          this.$http
            .post("api/client", this.client)
            .then((response) => {
              swal(this.client.name, "was successfully created!", {
                icon: "success",
              });
              this.$router.push({
                path: "/client",
              });
            })
            .catch((response) => {
              swal({
                title: "Error",
                text: response.body.error,
                icon: "error",
                dangerMode: true,
              }).then((value) => {
                if (value) {
                  this.$refs.name.focus();
                }
              });
            });
        }
      });
    },

    exit() {
      swal("Are you sure you want to go back?", {
        icon: "warning",
        buttons: {
          exit: "Yes",
          cancel: true,
        },
      }).then((value) => {
        switch (value) {
          case "exit":
            this.$router.push({
              path: "/client",
            });
            break;

          default:
            break;
        }
      });
    },
  },
};
</script>


<style scoped>
textarea {
  resize: none;
}
</style>
