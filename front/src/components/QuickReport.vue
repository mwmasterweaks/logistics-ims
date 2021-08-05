<template>
  <div class="container-fluid">
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
                title="Generate Summary"
              >
                <i class="material-icons">summarize</i>
              </button>

              <button
                type="submit"
                class="btn bg-black waves-effect waves-light"
                title="Print Preview"
                @click.prevent="print"
              >
                <i class="material-icons">print</i>
              </button>
              <button
                type="submit"
                class="btn bg-black waves-effect waves-light"
                title="Export to Excel"
                @click="deliveryExcel('summaryTable1')"
                v-if="filterBy == 'deliverySum'"
              >
                <i class="material-icons">publish</i>
              </button>
              <button
                type="submit"
                class="btn bg-black waves-effect waves-light"
                title="Export to Excel"
                @click="returnExcel('summaryTable2')"
                v-if="filterBy == 'salesReturn'"
              >
                <i class="material-icons">publish</i>
              </button>
              <button
                type="submit"
                class="btn bg-black waves-effect waves-light"
                title="Export to Excel"
                @click="itemsExcel('summaryTable3')"
                v-if="filterBy == 'items'"
              >
                <i class="material-icons">publish</i>
              </button>
            </div>
          </div>

          <!-- TEMPLATES -->
          <div style="width:100%">
            <div
              class="modal fade"
              id="summary"
              tabindex="-1"
              role="dialog"
              data-keyboard="false"
            >
              <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <h4>GENERATE QUICK REPORT</h4>
                    <button
                      type="button"
                      class="close"
                      data-dismiss="modal"
                      aria-label="Close"
                      id="dismiss"
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
                          <select
                            v-model="filterBy"
                            style="background:#e4e4e4;"
                          >
                            <option value="purchaseSum">Purchase Orders</option>
                            <option value="deliverySum"
                              >Delivery Receipts</option
                            >
                            <option value="salesReturn">Sales Returns</option>
                            <option value="items">Items</option>
                          </select>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-25">
                          <label class="labelNew">Select Supplier</label>
                        </div>
                        <div class="col-75">
                          <div style="width:92%;float:left">
                            <model-list-select
                              :list="suppliers"
                              v-model="supplierSelected"
                              option-value="id"
                              option-text="name"
                              style="background:#e4e4e4;"
                            ></model-list-select>
                          </div>
                          <div style="width:8%;float:left">
                            <button
                              class="btn btn-success waves-effect"
                              title="Clear Supplier"
                              @click="resetSupplier"
                            >
                              <i class="material-icons">backspace</i>
                            </button>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-25">
                          <label for="subject">Select Date Range</label>
                        </div>
                        <div class="col-75">
                          <date-range-picker @update="onDateSelected" />
                        </div>
                      </div>
                      <div></div>
                      <br />
                    </form>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-md-12" style="background:green;height:100%">
              <!-- PURCHASE ORDERS REPORT -->
              <div class="card" id="printable" v-if="filterBy == 'purchaseSum'">
                <div class="header text-center">
                  <img src="./../img/logo.jpg" width="200px" />
                  <br />
                  <br />

                  <h4 style="color:black">
                    PURCHASE ORDERS
                  </h4>
                  <h6>
                    As of
                    {{ this.sumDateSelected.to | moment("MMMM Do YYYY") }}
                  </h6>
                </div>
                <div class="table-wrap">
                  <table
                    class="table table-striped table-condensed"
                    id="summaryTable1"
                  >
                    <thead>
                      <tr>
                        <th>P.O No.</th>
                        <th>Supplier</th>
                        <th>Total Amount</th>
                        <th>Created Date</th>
                        <th>Requested by</th>
                        <th>Status</th>
                      </tr>
                    </thead>
                    <tbody>
                      <template>
                        <h4>
                          {{ this.sumDateSelected.to | moment("MMMM YYYY") }}
                        </h4>
                      </template>

                      <!-- DELIVERY RECEIPT  -->
                      <tr v-for="report in reports.purchase" :key="report.num">
                        <td>
                          <a
                            :href="'/purchase_order/' + report.num"
                            target="_blank"
                            >{{ report.number }}</a
                          >
                        </td>
                        <td>{{ report.supplier }}</td>
                        <td>{{ report.total }}</td>
                        <td>{{ report.date }}</td>
                        <td>{{ report.requestor }}</td>
                        <td>{{ report.status }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
              <!-- DELIVERY RECEIPTS REPORT -->
              <div class="card" id="printable" v-if="filterBy == 'deliverySum'">
                <div class="header text-center">
                  <img src="./../img/logo.jpg" width="200px" />
                  <br />
                  <br />

                  <h4 style="color:black">
                    FORMS AND DELIVERY RECEIPT
                  </h4>
                  <h6>
                    As of
                    {{ this.sumDateSelected.to | moment("MMMM Do YYYY") }}
                  </h6>
                </div>
                <div class="table-wrap">
                  <table
                    class="table table-striped table-condensed"
                    id="summaryTable1"
                  >
                    <thead>
                      <tr>
                        <th></th>
                        <th>Date</th>
                        <th>Number</th>
                        <th>Name</th>
                        <th>Memo</th>
                        <th>Class</th>
                        <th>Qty</th>
                      </tr>
                    </thead>
                    <tbody>
                      <template>
                        <h4>
                          {{ this.sumDateSelected.to | moment("MMMM YYYY") }}
                        </h4>
                      </template>

                      <!-- DELIVERY RECEIPT  -->
                      <tr
                        v-for="report in reports.data"
                        :key="report.dr_id"
                        :hidden="filterBy == 'salesSum'"
                      >
                        <td></td>
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
                          <p>Note:{{ report.memo }}</p>
                        </td>
                        <td>{{ report.class }}</td>
                        <td>
                          <p>-{{ report.qty }}</p>
                        </td>
                      </tr>
                      <template> </template>

                      <tr>
                        <td>
                          Total As of
                          {{ this.sumDateSelected.to | moment("MMMM Do YYYY") }}
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
              <!-- SALES RETURN REPORT -->
              <div class="card" id="printable" v-if="filterBy == 'salesReturn'">
                <div class="header text-center">
                  <img src="./../img/logo.jpg" width="200px" />
                  <br />
                  <br />

                  <h4 style="color:black">
                    SALES RETURNS REPORT
                  </h4>
                  <h6>
                    As of
                    {{ this.sumDateSelected.to | moment("MMMM Do YYYY") }}
                  </h6>
                </div>
                <div class="table-wrap">
                  <table
                    class="table table-striped table-condensed"
                    id="summaryTable2"
                  >
                    <thead>
                      <tr>
                        <th></th>
                        <th>Date</th>
                        <th>Item</th>
                        <th>Serial</th>
                        <th>Status</th>

                        <th>Qty</th>
                      </tr>
                    </thead>
                    <tbody>
                      <template>
                        <h4>
                          {{ this.sumDateSelected.to | moment("MMMM YYYY") }}
                        </h4>
                      </template>

                      <!-- SALES RETURN  -->
                      <tr
                        v-for="(report, index) in reports.data"
                        :key="index"
                        :hidden="filterBy != 'salesReturn'"
                      >
                        <td></td>
                        <td>
                          <p>{{ report.date }}</p>
                        </td>
                        <td>{{ report.id }}</td>
                        <td>
                          <p>{{ report.serial }}</p>
                        </td>
                        <td>
                          <p>{{ report.status }}</p>
                        </td>
                        <td>
                          <p>-{{ report.qty }}</p>
                        </td>
                      </tr>

                      <template> </template>

                      <tr>
                        <td>
                          Total As of
                          {{ this.sumDateSelected.to | moment("MMMM Do YYYY") }}
                        </td>
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
              <!-- ALL ITEMS REPORT -->
              <div class="card" id="printable" v-if="filterBy == 'items'">
                <div class="header text-center">
                  <img src="./../img/logo.jpg" width="200px" />
                  <br />
                  <br />

                  <h4 style="color:black">
                    PHYSICAL INVENTORY WORKSHEET
                  </h4>
                  <h6>
                    As of
                    {{ this.sumDateSelected.to | moment("MMMM Do YYYY") }}
                  </h6>
                </div>
                <div class="table-wrap">
                  <table
                    class="table table-striped table-condensed"
                    id="summaryTable3"
                  >
                    <thead>
                      <tr>
                        <th>Item</th>
                        <th>Description</th>
                        <th>Quantity On Hand</th>
                        <!-- <th>Physical Count</th> -->
                      </tr>
                    </thead>
                    <tbody>
                      <template>
                        <h4>
                          {{ this.sumDateSelected.to | moment("MMMM Do YYYY") }}
                        </h4>
                      </template>
                      <tr
                        v-for="(report, index) in reports.items"
                        :key="index"
                        :hidden="filterBy != 'items'"
                      >
                        <td>{{ report.stock_id }}</td>
                        <td>{{ report.stock_desc }}</td>
                        <td style="text-align:right">
                          <span style="float:left;width:100px">{{
                            report.stock_qty
                          }}</span>
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
</template>

<script>
import PreLoader from "./PreLoader.vue";
import VueRangedatePicker from "vue-rangedate-picker";
import { ModelListSelect } from "vue-search-select";
import DateRangePicker from "vue-mj-daterangepicker";
import JsonExcel from "vue-json-excel";
import { ListGroupPlugin } from "bootstrap-vue";
import moment from "moment";

export default {
  components: {
    "pre-loader": PreLoader,
    "rangedate-picker": VueRangedatePicker,
    "model-list-select": ModelListSelect,
    "rangedate-picker": VueRangedatePicker,
    "json-excel": JsonExcel
  },

  data() {
    return {
      loaded: false,
      reports: [],
      clients: [],
      suppliers: [],
      filterBy: "",
      clientSelected: null,
      supplierSelected: null,
      sumDateSelected: {},
      value: "",
      users: [],
      successful_order: [],
      sales_returns: [],
      roles: [],
      dataForExcel: []
    };
  },
  created() {
    this.user = this.$global.getUser();
    this.roles = this.$global.getRoles();
    this.suppliers = this.$global.getSupplier();
  },
  mounted() {},

  methods: {
    print() {
      this.$htmlToPaper("printable");
    },
    deliveryExcel(tbl) {
      this.$nextTick(function() {
        setTimeout(
          function() {
            var tab_text =
              "<table><tr><th colspan='2' style='font-size: large;'>Forms and Delivery Receipts</th></tr>" +
              "<tr></tr><tr>" +
              "<td>From: " +
              moment(String(this.sumDateSelected.from)).format("MM/DD/YYYY") +
              " To: " +
              moment(String(this.sumDateSelected.to)).format("MM/DD/YYYY") +
              "</td>" +
              "</tr>" +
              "<tr>" +
              "<td>" +
              "</td>" +
              "</tr>";
            var textRange;
            var j = 0;
            var tab = document.getElementById(tbl); // id of table
            // var tab1 = document.getElementById("summaryTable1");

            // for (j = 0; j < tab1.rows.length; j++) {
            //   tab_text = tab_text + tab1.rows[j].innerHTML + "</tr>";
            // }
            tab_text = tab_text + "<tr></tr> <tr></tr>";
            var j = 0;
            for (j = 0; j < tab.rows.length; j++) {
              tab_text = tab_text + tab.rows[j].innerHTML + "</tr>";
            }

            tab_text = tab_text + "</table>";
            tab_text = tab_text.replace(/<A[^>]*>|<\/A>/g, ""); //remove if u want links in your table
            tab_text = tab_text.replace(/<img[^>]*>/gi, ""); // remove if u want images in your table
            tab_text = tab_text.replace(/<input[^>]*>|<\/input>/gi, ""); // reomves input params

            var ua = window.navigator.userAgent;
            var msie = ua.indexOf("MSIE ");

            if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)) {
              // If Internet Explorer
              txtArea1.document.open("txt/html", "replace");
              txtArea1.document.write(tab_text);
              txtArea1.document.close();
              txtArea1.focus();
              var sa = txtArea1.document.execCommand(
                "SaveAs",
                true,
                "Say Thanks to Sumit.xls"
              );
            } //other browser not tested on IE 11
            else
              var sa = window.open(
                "data:application/vnd.ms-excel," + encodeURIComponent(tab_text)
              );
            return sa;
          }.bind(this),
          1000
        );
      });
    },
    returnExcel(tbl) {
      this.$nextTick(function() {
        setTimeout(
          function() {
            var tab_text =
              "<table><tr><th colspan='2' style='font-size: large;'>Sales Returns Report</th></tr>" +
              "<tr></tr><tr>" +
              "<td>From: " +
              moment(String(this.sumDateSelected.from)).format("MM/DD/YYYY") +
              " To: " +
              moment(String(this.sumDateSelected.to)).format("MM/DD/YYYY") +
              "</td>" +
              "</tr>" +
              "<tr>" +
              "<td>" +
              "</td>" +
              "</tr>";
            var textRange;
            var j = 0;
            var tab = document.getElementById(tbl); // id of table
            // var tab1 = document.getElementById("summaryTable2");

            // for (j = 0; j < tab1.rows.length; j++) {
            //   tab_text = tab_text + tab1.rows[j].innerHTML + "</tr>";
            // }
            tab_text = tab_text + "<tr></tr> <tr></tr>";
            var j = 0;
            for (j = 0; j < tab.rows.length; j++) {
              tab_text = tab_text + tab.rows[j].innerHTML + "</tr>";
            }

            tab_text = tab_text + "</table>";
            tab_text = tab_text.replace(/<A[^>]*>|<\/A>/g, ""); //remove if u want links in your table
            tab_text = tab_text.replace(/<img[^>]*>/gi, ""); // remove if u want images in your table
            tab_text = tab_text.replace(/<input[^>]*>|<\/input>/gi, ""); // reomves input params

            var ua = window.navigator.userAgent;
            var msie = ua.indexOf("MSIE ");

            if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)) {
              // If Internet Explorer
              txtArea1.document.open("txt/html", "replace");
              txtArea1.document.write(tab_text);
              txtArea1.document.close();
              txtArea1.focus();
              var sa = txtArea1.document.execCommand(
                "SaveAs",
                true,
                "Say Thanks to Sumit.xls"
              );
            } //other browser not tested on IE 11
            else
              var sa = window.open(
                "data:application/vnd.ms-excel," + encodeURIComponent(tab_text)
              );
            return sa;
          }.bind(this),
          1000
        );
      });
    },
    itemsExcel(tbl) {
      this.$nextTick(function() {
        setTimeout(
          function() {
            var tab_text =
              "<table><tr><th colspan='2' style='font-size: large;'>PHYSICAL INVENTORY WORKSHEET</th></tr>" +
              "<tr></tr><tr>" +
              "<td>From: " +
              moment(String(this.sumDateSelected.from)).format("MM/DD/YYYY") +
              " To: " +
              moment(String(this.sumDateSelected.to)).format("MM/DD/YYYY") +
              "</td>" +
              "</tr>" +
              "<tr>" +
              "<td>" +
              "</td>" +
              "</tr>";
            var textRange;
            var j = 0;
            var tab = document.getElementById(tbl); // id of table
            // var tab1 = document.getElementById("summaryTable3");

            // for (j = 0; j < tab1.rows.length; j++) {
            //   tab_text = tab_text + tab1.rows[j].innerHTML + "</tr>";
            // }
            tab_text = tab_text + "<tr></tr> <tr></tr>";
            var j = 0;
            for (j = 0; j < tab.rows.length; j++) {
              tab_text = tab_text + tab.rows[j].innerHTML + "</tr>";
            }

            tab_text = tab_text + "</table>";
            tab_text = tab_text.replace(/<A[^>]*>|<\/A>/g, ""); //remove if u want links in your table
            tab_text = tab_text.replace(/<img[^>]*>/gi, ""); // remove if u want images in your table
            tab_text = tab_text.replace(/<input[^>]*>|<\/input>/gi, ""); // reomves input params

            var ua = window.navigator.userAgent;
            var msie = ua.indexOf("MSIE ");

            if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)) {
              // If Internet Explorer
              txtArea1.document.open("txt/html", "replace");
              txtArea1.document.write(tab_text);
              txtArea1.document.close();
              txtArea1.focus();
              var sa = txtArea1.document.execCommand(
                "SaveAs",
                true,
                "Say Thanks to Sumit.xls"
              );
            } //other browser not tested on IE 11
            else
              var sa = window.open(
                "data:application/vnd.ms-excel," + encodeURIComponent(tab_text)
              );
            return sa;
          }.bind(this),
          1000
        );
      });
    },

    onDateSelected(values) {
      this.sumDateSelected = values;
      this.getSummary();
    },
    getSummary() {
      this.$root.$emit("pageLoading");
      this.sumDateSelected.filterBy = this.filterBy;
      this.sumDateSelected.supplierSelected = this.supplierSelected;

      console.log(this.sumDateSelected);
      this.$http
        .post("api/dashboard/showItemInventoryReport", this.sumDateSelected)
        .then(response => {
          console.log(response.body);
          this.reports = response.body;
        });
      this.$root.$emit("pageLoaded");
      document.getElementById("dismiss").click();
    },
    resetSupplier() {
      this.supplierSelected = "";
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
  border-radius: 4px;
  resize: vertical;
}

.labelNew {
  padding: 12px 12px 12px 0;
  display: inline-block;
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
