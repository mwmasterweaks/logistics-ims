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

          <!-- TEMPLATES -->
          <div style="width:100%">
            <div
              class="modal fade"
              id="summary"
              tabindex="-1"
              role="dialog"
              data-backdrop="static"
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
                          <select v-model="filterBy" @change="getSummary">
                            <option value="deliverySum"
                              >Delivery Receipt</option
                            >
                            <option value="salesReturn">Sales Returns</option>
                          </select>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-25">
                          <label for="subject">Select Date Range</label>
                        </div>
                        <div class="col-75">
                          <!-- <rangedate-picker
                            style="color:black; background-color:white"
                            i18n="EN"
                            @selected="onDateSelected"
                          ></rangedate-picker> -->
                          <date-range-picker @update="onDateSelected" />
                        </div>
                      </div>
                      <div></div>
                      <br />

                      <!-- <div style="float:right">
                        <button
                          class="btn btn-success waves-effect"
                          data-dismiss="modal"
                        >
                          Done
                        </button>
                      </div> -->
                    </form>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-md-12" style="background:green;height:100%">
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
                    {{ this.sumDateSelected.end | moment("MMMM Do YYYY") }}
                  </h6>
                </div>
                <div class="table-wrap">
                  <table class="table table-striped table-condensed">
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
                          {{ this.sumDateSelected.end | moment("MMMM YYYY") }}
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
                          {{
                            this.sumDateSelected.end | moment("MMMM Do YYYY")
                          }}
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
                    {{ this.sumDateSelected.end | moment("MMMM Do YYYY") }}
                  </h6>
                </div>
                <div class="table-wrap">
                  <table class="table table-striped table-condensed">
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
                          {{ this.sumDateSelected.end | moment("MMMM YYYY") }}
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
                          {{
                            this.sumDateSelected.end | moment("MMMM Do YYYY")
                          }}
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

export default {
  components: {
    "pre-loader": PreLoader,
    "rangedate-picker": VueRangedatePicker,
    "model-list-select": ModelListSelect,
    "rangedate-picker": VueRangedatePicker
  },

  data() {
    return {
      loaded: false,
      reports: [],
      clients: [],
      filterBy: "",
      clientSelected: null,
      sumDateSelected: {},
      value: "",
      users: [],
      successful_order: [],
      sales_returns: [],
      roles: []
    };
  },
  created() {
    this.user = this.$global.getUser();
    this.roles = this.$global.getRoles();
  },
  mounted() {},

  methods: {
    print() {
      this.$htmlToPaper("printable");
    },

    // onDateSelected(dateSelected) {
    //   this.sumDateSelected = dateSelected;
    //   console.log(this.sumDateSelected.end);
    //   this.getSummary();
    // },
    onDateSelected(values) {
      this.$root.$emit("pageLoading");
      console.log(values);
      this.sumDateSelected = values;
      this.getSummary();
    },
    getSummary() {
      this.sumDateSelected.filterBy = this.filterBy;

      console.log(this.sumDateSelected);
      this.$http
        .post("api/dashboard/showItemInventoryReport", this.sumDateSelected)
        .then(response => {
          console.log(response.body);
          this.reports = response.body;
        });
      this.$root.$emit("pageLoaded");
      document.getElementById("dismiss").click();
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
/*
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
} */

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
