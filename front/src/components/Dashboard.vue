<template>
  <div class="container-fluid">
    <pre-loader></pre-loader>

    <!-- Summary reports-->
    <div class="row clearfix">
      <!-- stock monitoring table -->
      <div class="col-md-12" style="max-height:500px">
        <div class="card" style="height:100%;overflow-y:scroll">
          <div class="header">
            <h2>Latest Forecast</h2>
          </div>
          <div class="table-wrap">
            <!-- running low forecast -->
            <div style="padding:0;width:49.9%;float:left">
              <div style="height:60px; background:#f8f8f8;">
                <label
                  class="gradient"
                  style="color:#d46900;padding-left:12px;letter-spacing: 0.05em;display:block;display: table-cell; vertical-align: middle;height:42px"
                  >Running Low</label
                >
                <label
                  style="display:block;background: linear-gradient(to right, green, orange); height:8px"
                ></label>
              </div>
              <table class="table table-borderless">
                <tbody>
                  <tr
                    v-for="alert in alerts"
                    :key="alert.id"
                    v-show="alert.totalItem > 0 && alert.status == 'no'"
                  >
                    <td>
                      <a
                        :href="'/items/' + alert.item_id + '/edit'"
                        target="_blank"
                      >
                        {{ alert.item_id }}
                      </a>
                    </td>
                    <td>
                      <p>{{ alert.description }}</p>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- out of stocks forecast -->

            <div style="padding:0;width:49.9%;float:right">
              <div style="height:60px; background:#f8f8f8;">
                <label
                  class="gradient"
                  style="color:#cc2b00;padding-left:12px;letter-spacing: 0.05em;display:block;display: table-cell; vertical-align: middle;height:42px"
                  >Out of Stock</label
                >
                <label
                  style="display:block;background: linear-gradient(to right, orange, #cc2b00); height:8px"
                ></label>
              </div>
              <table class="table table-borderless">
                <tbody>
                  <tr
                    v-for="alert in alerts"
                    :key="alert.id"
                    v-show="alert.totalItem < 1 && alert.status == 'no'"
                  >
                    <td style="display: table-cell; vertical-align: middle;">
                      <a
                        :href="'/items/' + alert.item_id + '/edit'"
                        target="_blank"
                      >
                        {{ alert.item_id }}
                      </a>
                    </td>
                    <td>
                      <p>{{ alert.description }}</p>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
    <br />
    <br />

    <!-- QUICK REPORT -->
    <div class="row clearfix">
      <div class="col-md-12">
        <div class="card">
          <div class="header" style="display:block">
            <h2 style="float:left">Quick Report</h2>

            <div style="float:right">
              <button
                class="btn btn-success waves-effect"
                data-toggle="modal"
                data-target="#summary"
              >
                Create Report
              </button>
              <button
                type="submit"
                class="btn bg-black waves-effect waves-light"
                @click.prevent="print"
              >
                Print
              </button>
            </div>
          </div>

          <div
            class="modal fade"
            id="summary"
            tabindex="-1"
            role="dialog"
            data-backdrop="static"
            data-keyboard="false"
          >
            <!-- data-backdrop="static"
        data-keyboard="false" -->
            <div class="modal-dialog modal-lg" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h4>GENERATE QUICK REPORT</h4>
                  <button
                    type="button"
                    class="close"
                    data-dismiss="modal"
                    aria-label="Close"
                  >
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <form>
                    <div class="row">
                      <div class="col-25">
                        <label class="labelNew">Filter By</label>
                      </div>
                      <div class="col-75">
                        <select v-model="filterBy" @change="getSummary">
                          <option value="deliverySum">Delivery Receipt</option>
                          <option value="clientSum">Clients</option>
                          <option value="itemSum">Items</option>
                          <option value="salesSum">Sales Orders</option>
                        </select>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-25">
                        <label for="subject">Select Date Range</label>
                      </div>
                      <div class="col-75">
                        <rangedate-picker
                          style="color:black; background-color:white"
                          i18n="EN"
                          @selected="onDateSelected"
                        ></rangedate-picker>
                      </div>
                    </div>
                    <div class="row" v-if="filterBy == 'clientSum'">
                      <div class="col-25">
                        <label>Select Client</label>
                      </div>
                      <div class="col-75">
                        <!-- <select v-model="clientSelected" @change="getSummary">
                          <option
                            v-for="client in clients"
                            :value="client.id"
                            :key="client.id"
                            >{{ client.name }}</option
                          >
                        </select> -->
                        <model-list-select
                          :list="clients"
                          v-model="clientSelected"
                          option-value="id"
                          option-text="name"
                          placeholder="-- Select Client --"
                          style="background:gray"
                        ></model-list-select>
                      </div>
                    </div>

                    <div></div>
                    <br />

                    <div style="float:right">
                      <button
                        class="btn btn-success waves-effect"
                        data-dismiss="modal"
                      >
                        Done
                      </button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-12">
            <div class="card" id="printable">
              <div class="header text-center">
                <img src="./../img/logo.jpg" width="150px" />
                <br />
                <br />

                <h4 style="color:black">
                  INVENTORY ITEM QUICK REPORT
                </h4>
                <h6>
                  As of {{ this.sumDateSelected.end | moment("MMMM Do YYYY") }}
                </h6>
              </div>
              <div class="table-wrap">
                <table class="table table-striped table-condensed">
                  <thead>
                    <tr>
                      <th></th>
                      <th>Type</th>
                      <th>Date</th>
                      <th>Number</th>
                      <th>Name</th>
                      <th>Description</th>
                      <th>Qty</th>
                    </tr>
                  </thead>
                  <tbody>
                    <template>
                      <h4>Inventory</h4>
                    </template>

                    <!-- DELIVERY RECEIPT  -->
                    <tr
                      v-for="report in reports.data"
                      :key="report.id"
                      :hidden="filterBy == 'salesSum'"
                    >
                      <td></td>
                      <td>Invoice</td>
                      <td>
                        <p>{{ report.date }}</p>
                      </td>
                      <td>
                        <a
                          :href="'/delivery_receipt/' + report.dr_id"
                          target="_blank"
                          >{{ report.dr_id }}</a
                        >
                      </td>
                      <td>
                        <p>{{ report.name }}</p>
                      </td>
                      <td>
                        <p>{{ report.desc }}</p>
                      </td>
                      <td>
                        <p>-{{ report.qty }}</p>
                      </td>
                    </tr>
                    <tr :hidden="filterBy == 'salesSum'">
                      <td :hidden="filterBy == 'salesSum'">
                        Total On Hand As of
                        {{ this.sumDateSelected.end | moment("MMMM Do YYYY") }}
                      </td>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td>-{{ reports.totalQty }}</td>
                    </tr>
                    <template> </template>
                    <tr
                      v-for="report in reports.data"
                      :key="report.id"
                      :hidden="filterBy == 'deliverySum'"
                    >
                      <td></td>
                      <td>Sales Order</td>
                      <td>
                        <p>{{ report.date }}</p>
                      </td>
                      <td>
                        <a
                          :href="'/sales_order/' + report.sales_order_id"
                          target="_blank"
                        >
                          {{ report.sales_order_id }}
                        </a>
                      </td>
                      <td>
                        <p>{{ report.name }}</p>
                      </td>
                      <td>
                        <p>{{ report.desc }}</p>
                      </td>
                      <td>
                        <p>-{{ report.qty }}</p>
                      </td>
                    </tr>
                    <tr :hidden="filterBy == 'deliverySum'">
                      <td>
                        Total On Sales Order As of
                        {{ this.sumDateSelected.end | moment("MMMM Do YYYY") }}
                      </td>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td>-{{ reports.totalQty }}</td>
                    </tr>
                    <tr>
                      <td>
                        Total As of
                        {{ this.sumDateSelected.end | moment("MMMM Do YYYY") }}
                      </td>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td>-{{ reports.totalQty }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import PreLoader from "./PreLoader.vue";
import VueRangedatePicker from "vue-rangedate-picker";
import { ModelListSelect } from "vue-search-select";

export default {
  components: {
    "pre-loader": PreLoader,
    "rangedate-picker": VueRangedatePicker,
    "model-list-select": ModelListSelect
  },

  data() {
    return {
      reports: [],
      clients: [],
      filterBy: "",
      clientSelected: null,
      sumDateSelected: {},
      value: "",
      clients: [],
      users: [],
      items: [],
      successful_order: [],
      sales_returns: [],

      items: [],
      alerts: [],
      notifications: [],
      roles: [],
      user: [],
      alerts: []
    };
  },
  created() {
    this.user = this.$global.getUser();
    this.load();
    this.roles = this.$global.getRoles();
    this.getClients();
  },
  mounted() {
    //this.setCount();
    // channel.bind("NotifyCreatedSalesOrder", this.liveUpdate);
    // channel.bind("NotifyUpdatedSalesOrder", this.liveUpdate);
  },

  methods: {
    load() {
      this.$http.post("api/sales_order/alert").then(response => {
        this.alerts = response.body;
      });

      this.$http.get("api/users/" + this.user.id).then(response => {
        this.$global.setRoles(response.body.roles);
        this.roles = this.$global.getRoles();
        $(".page-loader-wrapper").fadeOut();
      });
    },
    linkGen(pageNum) {
      return pageNum === 1 ? "?" : `?page=${pageNum}`;
    },

    print() {
      this.$htmlToPaper("printable");
    },

    done() {
      // $("#summary").modal("hide");
    },

    getClients() {
      this.$http.get("api/client").then(response => {
        this.clients = response.body;
      });
    },
    onDateSelected(dateSelected) {
      this.sumDateSelected = dateSelected;
      console.log(this.sumDateSelected.end);
      this.getSummary();
    },

    getSummary() {
      this.sumDateSelected.filterBy = this.filterBy;

      console.log(this.sumDateSelected);
      if (this.sumDateSelected.start != null) {
        if (this.filterBy == "itemSum") {
          this.$http
            .post("api/dashboard/showItemInventoryReport", this.sumDateSelected)
            .then(response => {
              console.log(response.body);
              this.reports = response.body;
            });
        } else if (
          this.filterBy == "clientSum" &&
          this.clientSelected != null
        ) {
          this.sumDateSelected.client_id = this.clientSelected;
          console.log(this.sumDateSelected);
          this.$http
            .post("api/dashboard/showItemInventoryReport", this.sumDateSelected)
            .then(response => {
              console.log(response.body);
              this.reports = response.body;
            });
        } else {
          this.$http
            .post("api/dashboard/showItemInventoryReport", this.sumDateSelected)
            .then(response => {
              console.log(response.body);
              this.reports = response.body;
            });
        }
      }
    }
  }
};
</script>

<style>
* {
  box-sizing: border-box;
}

.input-date {
  display: block;
  border: 1px solid rgb(1, 235, 71) !important;
  padding: 5px;
  font-size: 15px !important;
  width: 100% !important;
  cursor: pointer;
}

select,
textarea {
  width: 100%;
  padding: 5px;
  border: 1px solid rgb(1, 235, 71);
  border-radius: 4px;
  resize: vertical;
}

.labelNew {
  padding: 12px 12px 12px 0;
  display: inline-block;
}

input[type="submit"] {
  background-color: #4caf50;
  color: white;
  padding: 12px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  float: right;
}

input[type="submit"]:hover {
  background-color: #45a049;
}

.container {
  border-radius: 5px;
  background-color: #f2f2f2;
  padding: 20px;
}

.col-25 {
  float: left;
  width: 25%;
  margin-top: 6px;
}

.col-75 {
  float: left;
  width: 75%;
  margin-top: 6px;
}

/* Responsive layout - when the screen is less than 600px wide, make the two columns stack on top of each other instead of next to each other */
@media screen and (max-width: 600px) {
  .col-25,
  .col-75,
  input[type="submit"] {
    width: 100%;
    margin-top: 0;
  }
}

.button {
  background-color: #4caf50; /* Green */
  border: none;
  color: white;
  padding: 15px 32px;
  text-align: center;
  text-decoration: none;
  display: inline-block;
  font-size: 16px;
  margin: 4px 2px;
  cursor: pointer;
  -webkit-transition-duration: 0.4s; /* Safari */
  transition-duration: 0.4s;
}
</style>
