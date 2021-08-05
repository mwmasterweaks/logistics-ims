<template>
  <div class="container-fluid">
    <div class="row clearfix">
      <div class="col-lg-12 col-md-12">
        <div class="block-header">
          <button
            type="button"
            class="btn btn-default waves-effect"
            @click="exit"
          >
            <i class="material-icons">keyboard_backspace</i>
            <span>Back</span>
          </button>

          <div
            :hidden="
              dataStock.status != 'For Approval' || !roles.approved_sales_order
            "
            style="float:right; display:block"
          >
            <button
              type="button"
              class="btn btn-lg btn-success waves-effect"
              @click="accept"
            >
              <span>Accept</span>
            </button>
            <!-- <button type="button" class="btn btn-lg btn-danger waves-effect">
              <span>Decline</span>
            </button> -->
          </div>
        </div>
        <div class="card">
          <div class="body">
            <div class="row clearfix">
              <div class="col-md-12 col-sm-12">
                <h4>Sales Return #{{ dataStock.id }}</h4>
              </div>
            </div>
            <hr />
            <div class="row clearfix">
              <div class="col-md-2">
                <b>Date Recieve:</b>
                <input
                  class="form-control"
                  type="text"
                  v-model="dataStock.date_recieve"
                  readonly
                />
              </div>

              <div class="col-md-2">
                <b>Returnee:</b>
                <br />
                <input
                  class="form-control"
                  type="text"
                  v-model="dataStock.returnee"
                  readonly
                />
              </div>
              <div class="col-md-3">
                <b>From:</b>
                <br />
                <strong>{{ dataStock.from_name }}</strong>
                <br />
                <span>{{ dataStock.from_address }}</span>
                <br />
                <span>{{ dataStock.from_contact }}</span>
              </div>

              <div class="col-md-3">
                <b>To:</b>
                <br />
                <strong>Logistics</strong>
              </div>
            </div>

            <!-- START Return TABLE -->
            <div class="row clearfix">
              <div class="col-md-12">
                <div class="table-wrap">
                  <div class="table-responsive">
                    <table class="table table-striped">
                      <thead>
                        <tr>
                          <th>Code</th>
                          <th>serial</th>
                          <th>Name</th>
                          <th>Description</th>
                          <th>Qty</th>
                          <th>Status</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="item in itemsDis" :key="item.index">
                          <td>
                            {{ item.id }}
                          </td>
                          <td>{{ item.serial }}</td>
                          <td>{{ item.name }}</td>
                          <td>{{ item.desc }}</td>
                          <td>{{ item.qty }}</td>
                          <!-- <td>{{ item.status }}</td> -->
                          <td :hidden="dataStock.status != 'For Approval'">
                            <select
                              class="form-control"
                              v-model="data.status"
                              @change="updateStatus"
                            >
                              <option value="Stocked in">Stocked in</option>
                              <option value="Defective">Defective</option>
                              <option value="For repair">For repair</option>
                              <option value="Refurbish">Refurbish</option>
                              <option value="Under observation"
                                >Under observation</option
                              >
                              <option value="Stock transfer"
                                >Stock transfer</option
                              >
                            </select>
                          </td>
                          <td :hidden="dataStock.status == 'For Approval'">
                            {{ item.status }}
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
            <!-- END Return TABLE -->

            <!-- REMARKS -->
            <div class="row clearfix">
              <div class="col-md-6">
                <span>Remarks:</span>
                <textarea
                  type="text"
                  class="form-control"
                  rows="5"
                  v-model="dataStock.remarks"
                  readonly
                ></textarea>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
var moment = require("moment");
moment().format();

import swal from "sweetalert";
import PreLoader from "../PreLoader.vue";

export default {
  data() {
    return {
      itemsDis: [],
      roles: [],
      dataStock: {
        id: null,
        from_name: "",
        from_contact: "",
        from_address: "",
        status: "",
        clientTo: [],
        date_recieve: "",
        returnee: "",
        remarks: ""
      },
      data: {
        status: ""
      }
    };
  },

  created() {
    this.roles = this.$global.getRoles();
  },
  beforeMount() {
    this.roles = this.$global.getRoles();
  },

  mounted() {
    this.loadData();
    // console.log(this.itemsDis);
  },

  methods: {
    loadData() {
      this.$http
        .get("api/SalesReturns/" + this.$route.params.sales_return_id)
        .then(response => {
          //console.log(response.body);
          this.dataStock.id = this.$route.params.sales_return_id;
          this.dataStock.from_name = response.body.from_name;
          this.dataStock.from_contact = response.body.from_contact;
          this.dataStock.from_address = response.body.from_address;

          this.dataStock.clientTo.name = response.body.to_name;
          this.dataStock.clientTo.contact = response.body.to_contact;
          this.dataStock.clientTo.address = response.body.to_address;
          this.dataStock.status = response.body.status;

          this.dataStock.date_recieve = response.body.date_return;
          this.dataStock.returnee = response.body.returnee;
          this.dataStock.remarks = response.body.remarks;
          let items = [];
          response.body.items.forEach(function(item) {
            if (item.serial != null) {
              items.push({
                id: item.id,
                name: item.name,
                desc: item.description,
                qty: item.qty,
                status: item.status,
                type: "serial",
                serial: item.serial
              });
            } else {
              items.push({
                id: item.id,
                name: item.name,
                desc: item.description,
                qty: item.qty,
                status: item.status,
                type: "consumable",
                serial: ""
              });
            }
          });
          this.itemsDis = items;
          //console.log(items);
        });
    },
    updateStatus() {
      if (this.data.status != null) {
        var data = {
          id: this.dataStock.id,
          itemStatus: this.data.status,
          item: this.itemsDis
        };

        console.log(data);
        this.$http
          .post("api/sales_return/updateStatus", data)
          .then(response => {
            // this.$global.setSalesReturn(response.body);
            // swal(this.category.name, "has successfully updated!", {
            //   icon: "success"
            // });
            // this.category = {};
            console.log(response.body);
          });
      }
    },

    accept() {
      this.$validator.validateAll().then(result => {
        if (result) {
          this.$http
            .post("api/sales_return/accept/" + this.dataStock.id)
            .then(response => {
              console.log(response.body);
              if (response.body == "Data doesn't exist!!") {
                swal({
                  title: "Error",
                  text: response.body,
                  icon: "error",
                  dangerMode: true
                });
              } else {
                swal("Item Returned.", {
                  icon: "success"
                });
              }

              this.loadData();
              // this.$global.setSalesReturn(response.body);

              // swal("Sales Return #" + this.dataStock.id + " accepted!", {
              //   icon: "success"
              // });
            });
        }
      });
    },

    exit() {
      swal("Are you sure you want to go back?", {
        icon: "warning",
        buttons: {
          exit: "Yes",
          cancel: true
        }
      }).then(value => {
        switch (value) {
          case "exit":
            this.$router.push({
              path: "/list/sales_return"
            });
            break;

          default:
            break;
        }
      });
    }
  }
};
</script>

<style scoped>
#Stock_serialCode {
  display: none;
}

#serial_exist_tip {
  font-size: 10px;
  color: red;
}
input {
  width: 150px;
}
#inputQty {
  width: 50px;
}

textarea {
  resize: none;
}

.table-wrap {
  height: 500px;
  border: 1px solid #eee;
  overflow-y: auto;
  overflow-x: hidden;
}

.table-wrap2 {
  height: 200px;
  border: 1px solid #eee;
  overflow-y: auto;
  overflow-x: hidden;
}

/* width */
::-webkit-scrollbar {
  width: 5px;
}

/* Track */
::-webkit-scrollbar-track {
  background: #f1f1f1;
}

/* Handle */
::-webkit-scrollbar-thumb {
  background: #888;
}

/* Handle on hover */
::-webkit-scrollbar-thumb:hover {
  background: #555;
}
</style>
