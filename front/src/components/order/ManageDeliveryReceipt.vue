<template>
  <div class="container-fluid">
    <div class="block-header" id="sales_order_button" style="margin-top:-20px">
      <div class="row clearfix">
        <div class="col-lg-10 col-md-10">
          <json-excel
            :data="dataForExcel"
            class="btn btn-lg btn-default waves-effect"
          >
            <span>Export to Excel</span>
          </json-excel>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="header">
        <h2>Manage Delivery Receipts</h2>
      </div>
      <div class="body">
        <div class="row clearfix">
          <div class="col-md-5">
            <div class="form-group">
              <div class="form-line">
                <span>Search</span>
                <input
                  type="text"
                  class="form-control"
                  autocomplete="off"
                  @keyup="searchText"
                  v-model="search.text"
                />
              </div>
            </div>
          </div>
          <!--
          <div class="col-md-2">
            <b>Filter by Last Date Updated</b>

            <date-picker
              v-model="filter.updated_at"
              name="filter_updated_at"
              :config="options"
              autocomplete="off"
            ></date-picker>
          </div>

          <div class="col-md-2">
            <b>Filter by Date Created</b>

            <date-picker
              v-model="filter.created_at"
              name="filter_created_at"
              :config="options"
              autocomplete="off"
            ></date-picker>
          </div> -->
        </div>
        <div class="row clearfix">
          <div class="col-md-12">
            <!-- START ORDER LIST TABLE -->
            <div class="table-wrap">
              <div class="table-responsive">
                <table
                  class="table table-striped table-condensed table-hover"
                  id="itemTable"
                  ref="itemTable"
                >
                  <thead>
                    <tr>
                      <th>Delivery Receipt #</th>
                      <th>Client</th>
                      <th>Sales Order #</th>
                      <th>Status</th>
                      <th>Last Date Updated</th>
                      <th>Date Created</th>
                      <th>Created by</th>
                    </tr>
                  </thead>
                  <tbody>
                    <router-link
                      tag="tr"
                      :to="'/delivery_receipt/' + delivery_receipt.id"
                      v-for="delivery_receipt in delivery_receipts"
                      :key="delivery_receipt.id"
                      style="cursor: pointer;"
                    >
                      <td>{{ delivery_receipt.id }}</td>
                      <td>
                        <span v-if="delivery_receipt.client.length != 0">{{
                          delivery_receipt.client.name
                        }}</span>
                        <span v-else>
                          <small class="col-red">
                            <i>no client selected</i>
                          </small>
                        </span>
                      </td>
                      <td>{{ delivery_receipt.sales_order_id }}</td>
                      <td
                        class="bg-grey"
                        v-show="delivery_receipt.status == 'for delivery'"
                      >
                        <span>Order: For Delivery</span>
                      </td>
                      <td
                        class="bg-green"
                        v-show="delivery_receipt.status == 'delivering'"
                      >
                        <span>Order: On Shipped</span>
                      </td>
                      <td
                        class="bg-green"
                        v-show="delivery_receipt.status == 'delivered'"
                      >
                        <span>Order: Delivered</span>
                      </td>
                      <td>{{ delivery_receipt.updated_at }}</td>
                      <td>{{ delivery_receipt.created_at }}</td>
                      <td>{{ delivery_receipt.user.name }}</td>
                    </router-link>
                    <tr v-show="delivery_receipts.length == 0">
                      <td colspan="7" class="text-center">
                        <small class="col-red">
                          <i>No receipts found.</i>
                        </small>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
            <!-- END ORDER LIST TABLE-->
            <br />
            <p>{{ delivery_receipts.length }} items found.</p>
          </div>
        </div>
      </div>
      <div class="modal fade" id="summary" tabindex="-1" role="dialog">
        <!-- data-backdrop="static"
        data-keyboard="false" -->
        <div class="modal-dialog modal-lg" role="document">
          <div class="modal-content">
            <div class="modal-header">
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
                <div class="row clearfix">
                  <div class="col-md-5">
                    <div class="form-group">
                      <span>Select Date Range</span>
                      <rangedate-picker
                        style="color:black; background-color:white"
                        i18n="EN"
                        @selected="onDateSelected"
                      ></rangedate-picker>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <span>Filter by</span>
                      <div class="form-line">
                        <select
                          class="form-control"
                          v-model="filterBy"
                          @change="getSummary"
                        >
                          <option value="deliverySum">Delivery Receipt</option>
                          <option value="clientSum">Clients</option>
                          <option value="itemSum">Items</option>
                          <option value="salesSum">Sales Orders</option>
                        </select>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-3" v-if="filterBy == 'clientSum'">
                    <div class="form-group">
                      <span>Clients</span>
                      <div class="form-line">
                        <select
                          class="form-control"
                          v-model="clientSelected"
                          @change="getSummary"
                        >
                          <option
                            v-for="client in clients"
                            :value="client.id"
                            :key="client.id"
                            >{{ client.name }}</option
                          >
                        </select>
                      </div>
                    </div>
                  </div>
                </div>
              </form>

              <div class="row clearfix">
                <div class="col-md-14">
                  <div class="card">
                    <div class="header text-center">
                      <h3>DCTECH MICROSERVICES INC.</h3>
                      <!-- <br /> -->
                      <h4>INVENTORY ITEM QUICK REPORT</h4>
                    </div>
                    <div class="table-wrap">
                      <table class="table table-striped table-condensed">
                        <thead>
                          <tr>
                            <th>Date</th>
                            <th>Sales Order No.</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Qty</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr v-for="report in reports" :key="report.id">
                            <td>
                              <p>{{ report.date }}</p>
                            </td>
                            <td>
                              <p>{{ report.num }}</p>
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
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
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
import JsonExcel from "vue-json-excel";
moment().format();

import datePicker from "vue-bootstrap-datetimepicker";
import "pc-bootstrap4-datetimepicker/build/css/bootstrap-datetimepicker.css";
import VueRangedatePicker from "vue-rangedate-picker";

export default {
  components: {
    "json-excel": JsonExcel,
    datePicker,
    "rangedate-picker": VueRangedatePicker
  },
  data() {
    return {
      reports: [],
      clients: [],
      filterBy: "",
      clientSelected: null,
      sumDateSelected: {},
      search: {
        sort: "Delivery Receipts"
      },
      printData: {
        id: true,
        client: true,
        soNum: true,
        status: true,
        updated_at: false,
        created_at: false,
        created_by: false
      },
      filter: {
        updated_at: "",
        created_at: ""
      },
      search: {
        text: ""
      },
      options: {
        format: "YYYY-MM-DD",
        useCurrent: false
      },
      delivery_receipts: [],
      roles: [],
      dataForExcel: []
    };
  },

  beforeMount() {},
  mounted() {},

  created() {
    //this.onDateSelected();
    this.getDeliveryReceipts();
    this.roles = this.$global.getRoles();
    this.getClients();
  },

  methods: {
    getClients() {
      this.$http.get("api/client").then(response => {
        this.clients = response.body;
      });
    },
    onDateSelected(dateSelected) {
      this.sumDateSelected = dateSelected;
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
        }
        if (this.filterBy == "clientSum" && this.clientSelected != null) {
          this.sumDateSelected.client_id = this.clientSelected;
          console.log(this.sumDateSelected);
          this.$http
            .post("api/dashboard/showItemInventoryReport", this.sumDateSelected)
            .then(response => {
              console.log(response.body);
              this.reports = response.body;
            });
        }
      }
    },
    getDeliveryReceipts() {
      this.$http.get("api/delivery_receipt").then(response => {
        this.delivery_receipts = response.body;
        this.fetchDataaa();
      });
    },
    fetchDataaa() {
      this.dataForExcel = [];
      for (var i = 0; i < this.delivery_receipts.length; i++) {
        var status = "";
        var client = "No client selected";
        if (this.delivery_receipts[i].client.length != 0) {
          client = this.delivery_receipts[i].client.name;
        }
        if (this.delivery_receipts[i].status == "for delivery")
          status = "Order: For Delivery";
        if (this.delivery_receipts[i].status == "delivering")
          status = "Order: On Shipped";
        if (this.delivery_receipts[i].status == "delivered")
          status = "Order: Delivered";
        this.dataForExcel.push({
          "Delivery Receipt #": this.delivery_receipts[i].id,
          Client: client,
          "Sales Order #": this.delivery_receipts[i].sales_order_id,
          Status: status,
          "Last Date Updated": this.delivery_receipts[i].updated_at,
          "Date Created": this.delivery_receipts[i].created_at,
          "Created by": this.delivery_receipts[i].user.name
        });
      }
    },
    searchText() {
      var filter, table, tr, targetTableColCount;
      filter = this.search.text.toUpperCase();
      table = document.getElementById("itemTable");
      tr = table.getElementsByTagName("tr");

      for (var i = 0; i < tr.length - 1; i++) {
        var rowData = "";

        if (i == 0) {
          targetTableColCount = 9; //table.rows.item(i).cells.length;

          continue; //do not execute further code for header row.
        }
        for (var colIndex = 0; colIndex < targetTableColCount; colIndex++) {
          //console.log(table.rows.item(i).cells.item(colIndex).textContent);
          rowData += table.rows.item(i).cells.item(colIndex).textContent;
        }

        if (rowData.toUpperCase().indexOf(filter) == -1) {
          table.rows.item(i).style.display = "none";
        } else {
          table.rows.item(i).style.display = "table-row";
        }
      }
    },
    printPreview() {
      // $("#printModal").modal("hide");
      var divToPrint = this.$refs.itemTable;
      var newWin = window.open("");
      newWin.document.write(
        '<html><head><style>#itemTable {font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;border-collapse: collapse;  width: 100%;}#itemTable td, #customers th {  border: 1px solid #ddd;  padding: 8px;}#itemTable tr:nth-child(even){background-color: #f2f2f2;}#itemTable tr:hover {background-color: #ddd;}#itemTable th {  padding-top: 12px;  padding-bottom: 12px;  text-align: left;  background-color: #4CAF50;  color: white;}</style></head><body>'
      );
      newWin.document.write(divToPrint.outerHTML);

      newWin.document.write(
        "<p style='position: fixed; bottom: 10px;'>check by:________________</p>"
      );
      newWin.document.write("</body></html>");

      var tbl = newWin.document.getElementById("itemTable");

      for (var i = 0; i < tbl.rows.length - 1; i++) {
        // console.log(tbl.rows[i].cells[5].innerHTML);
        if (!this.printData.status) {
          if (i == 0) {
            tbl.rows[0].cells[3].style.display = "none";
          } else {
            tbl.rows[i].cells[3].style.display = "none";
            tbl.rows[i].cells[4].style.display = "none";
            tbl.rows[i].cells[5].style.display = "none";
          }
        }

        if (!this.printData.created_by) {
          if (i == 0) {
            tbl.rows[0].cells[6].style.display = "none";
          } else {
            tbl.rows[i].cells[8].style.display = "none";
          }
        }

        if (!this.printData.created_at) {
          if (i == 0) {
            tbl.rows[0].cells[5].style.display = "none";
          } else {
            tbl.rows[i].cells[7].style.display = "none";
          }
        }
        if (!this.printData.updated_at) {
          if (i == 0) {
            tbl.rows[0].cells[4].style.display = "none";
          } else {
            tbl.rows[i].cells[6].style.display = "none";
          }
        }

        if (!this.printData.soNum) {
          tbl.rows[i].cells[2].style.display = "none";
        }
        if (!this.printData.client) {
          tbl.rows[i].cells[1].style.display = "none";
        }
        if (!this.printData.id) {
          tbl.rows[i].cells[0].style.display = "none";
        }
      }
      newWin.print();
      newWin.close();
    }
  }
};
</script>

<style scoped>
.ready {
  font-size: 20px;
}

.modal {
  margin-top: 80px;
}

.table-wrap {
  height: 500px;
  overflow-y: auto;
  overflow-x: hidden;
  border: 1px solid #eee;
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
