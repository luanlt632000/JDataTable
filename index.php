<!DOCTYPE html>
<html lang="en">

<head>
  <title>Bootstrap Example</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.0.1/fonts/remixicon.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>

  <div class="container mt-3">
    <h2>Striped Rows</h2>
    <p>The .table-striped class adds zebra-stripes to a table:</p>

    <!-- Filter -->
    <div class="card mb-3">
      <div class="card-body">
        <h5 class="card-title">Filter</h5>
        <form id="filterForm">
          <div class="form-group">
            <label for="email">Email:</label>
            <input type="text" class="form-control" id="email" name="email">
          </div>
          <div class="form-group">
            <label for="code">Code:</label>
            <input type="text" class="form-control" id="code" name="code">
          </div>
          <button type="button" class="btn btn-primary mt-2" onclick="getFormValues()">Filter</button>
        </form>
      </div>
    </div>
    <!-- Filter -->

    <!-- Search -->
    <div class="form-group">
      <label for="search_input">Search:</label>
      <input id="search_input" type="text" class="form-control">
    </div>
    <!-- Search -->

    <!-- Table -->
    <table class="table table-striped table-bordered table-hover" id="bookTabe">
    </table>
    <!-- Table -->

    <!-- Pagination -->
    <div id="pagination"></div>
    <!-- Pagination -->

  </div>

  <script>
    class TableData {
      config = {
        order: null, // example order = { column : "name", orderBy : "ASC" }
        filter: null, // example filter = { name : "huynh" }
        heads: [], // config headers of table
        page: 1,
        per_page: 15,
        data: {}, // la data ben laravel tra ve
        rowRender: (colName, colValue, row) => {
          return colValue;
        },
        footer: {
          pagination: false,
          detail: false
        },
        sort: {
          enable: false,
          listColumn: []
        }
      };

      constructor(tableId, config) {
        this.config = {
          ...this.config,
          ...config
        };
        this.tableId = tableId;
      }

      // Update configuration, display data after sorting, listen again for appropriate icon
      sorted(column) {
        if (
          this.config.order !== null &&
          Object.values(this.config.order)[0] === "asc"
        ) {
          this.config.order = null;
          this.updateConfig(this.config);
        } else {
          if (this.config.order === null) {
            this.config.order = {
              [`order_by_${column}`]: "desc"
            };
            this.updateConfig(this.config);
            this.attachSortEventListeners(".ri-arrow-down-s-fill");
          } else {
            this.config.order = {
              [`order_by_${column}`]: "asc"
            };
            this.updateConfig(this.config);
            this.attachSortEventListeners(".ri-arrow-up-s-fill");
          }
        }
      }

      // Listen events of sort buttons
      attachSortEventListeners(className) {
        const icons = document.querySelectorAll(className);
        icons.forEach((icon) => {
          icon.addEventListener("click", (event) => {
            const columnName = event.target.dataset.columnName;
            this.sorted(columnName, icon);
          });
        });
      }

      // Show the sort icon again when the sort button is clicked
      renderIconSort(column) {
        if (this.config.order === null) {
          return `<i class="ri-expand-up-down-fill" style="cursor: pointer;" data-column-name="${column}"></i>`;
        } else {
          var orderKey = Object.keys(this.config.order)[0];
          var orderValue = Object.values(this.config.order)[0];
          if (orderKey === `order_by_${column}`) {
            if (orderValue === "asc") {
              return `<i class="ri-arrow-up-s-fill" style="cursor: pointer;" data-column-name="${column}"></i>`;
            } else {
              return `<i class="ri-arrow-down-s-fill" style="cursor: pointer;" data-column-name="${column}"></i>`;
            }
          } else {
            return "";
          }
        }
      }

      // Listen events of per page
      attachPerPageEventListeners() {
        const elements = document.querySelectorAll(`${this.tableId} #perpage div #perPageSelect`);
        elements[0].addEventListener("change", (event) => {
          const value = event.target.value;
          this.config.per_page = value
          this.render()
        });
      }

      // Listen events of pagination buttons
      attachPaginationEventListeners() {
        const pages = document.querySelectorAll(".page_number");
        pages.forEach((page) => {
          page.addEventListener("click", (event) => {
            const pageNumber = event.target.dataset.columnName;
            if (pageNumber !== "null" && pageNumber !== this.config.page) {
              this.config.page = pageNumber;
              this.render();
            }
          });
        });
      }

      // Listen events of action buttons
      attachActionEventListeners(className) {
        const buttons = document.querySelectorAll(className);
        buttons.forEach((btn) => {
          btn.addEventListener("click", (event) => {
            const rowId = event.target.dataset.rowId;
            if (rowId) {
              if (className === ".btn_edit") {
                this.rowEdit(rowId);
              }
              if (className === ".btn_done") {
                this.rowEditDone(rowId);
              }
            }
          });
        });
      }

      // Render headers in table
      renderHeads() {
        var ths = "";
        this.config.heads.map((head) => {
          if (head.name === "__actions") {
            ths += `<th scope="col">
                      <div class="d-flex justify-content-center align-items-center">
                        ${head.value}
                      </div>
                    </th>`;
          } else {
            if (head.hasOwnProperty("render")) {
              ths += `<th scope="col">
                    <div class="d-flex justify-content-center align-items-center">
                      ${head.render(head)}
                      ${this.config.sort.enable && 
                        this.config.sort.listColumn && 
                        this.config.sort.listColumn.includes(head.name) ? 
                        this.renderIconSort(head.name) : ''}
                    </div>
                  </th>`;
            } else {
              ths += `<th scope="col">
                    <div class="d-flex justify-content-center align-items-center">
                      ${head.value}
                      ${this.config.sort.enable && 
                        this.config.sort.listColumn && 
                        this.config.sort.listColumn.includes(head.name) ? 
                        this.renderIconSort(head.name) : ''}
                    </div>
                  </th>`;
            }
          }
        });
        var html = `<thead class="table-light text-center align-middle">
                  <tr>
                    ${ths}
                  <tr>
                </thead>`;
        return html;
      }

      // Render rows in table
      renderRows(data) {
        let html = "<tbody>";
        data.data.map((row, index) => {
          html += `<tr id='row_${row.id}'>`;
          this.config.heads.map((col) => {
            html += `<td ${col.name!=="__actions"?`title="${row[col.name]}"`:""}>${this.config.rowRender(
          col.name,
          row[col.name],
          row
        )}</td>`;
          });
          html += "</tr>";
        });
        html += "</tbody>";
        // Initial pagination use 'data.links'
        let htmlPagination = `<ul class="pagination">`;
        data.links.map((link) => {
          htmlPagination += `
      	<li class="page-item ${link.active ? "active" : ""}">
        	<span class="page_number page-link" data-column-name="${
            link.url !== null ? link.url.split("=")[1] : null
          }" style="cursor: pointer;">${link.label
        .replace(" Previous", "")
        .replace("Next ", "")}</span>
        </li>
      `;
        });
        htmlPagination += "</ul>";
        html += `<caption>
                <div class="d-flex align-items-center" style="justify-content: space-between;">`
        html += `<div id="perpage">
                <!-- Per page -->
                  <div class="d-flex align-items-center form-inline">
                    <label class="mr-2">Show</label>
                    <select id="perPageSelect" class="form-select form-select-sm me-2" style="font-size: 0.8rem; margin: 0 5px; cursor: pointer;">
                      <option value="15" ${this.config.per_page.toString() === "15" ? 'selected' : ''}>15</option>
                      <option value="30" ${this.config.per_page.toString() === "30" ? 'selected' : ''}>30</option>
                      <option value="50" ${this.config.per_page.toString() === "50" ? 'selected' : ''}>50</option>
                      <option value="80" ${this.config.per_page.toString() === "80" ? 'selected' : ''}>80</option>
                      <option value="100" ${this.config.per_page.toString() === "100" ? 'selected' : ''}>100</option>
                    </select>
                    <label style="width: auto;">entries</label>
                  </div>
                <!-- Per page -->
                </div>`
        if (this.config.footer.detail) {
          html += `<div id="footer_detail">Show ${data.from} to ${data.to} of ${data.total} entries</div>`
        }

        if (this.config.footer.pagination) {
          html += `<div id="footer_pagination">${htmlPagination}</div>`
        }


        html += `</div></caption>`
        $(this.tableId).append(html);
        this.attachPaginationEventListeners();
        this.attachPerPageEventListeners()
      }

      // Render table
      async render() {
        let data;
        let params = {
          per_page: this.config.per_page,
          page: this.config.page,
        };
        // Clear html
        $(this.tableId).html("");
        // Initial headers
        $(this.tableId).append(this.renderHeads());
        this.attachSortEventListeners(".ri-expand-up-down-fill");

        // Append 'order' and 'filter' into params if exist
        if (this.config.hasOwnProperty("ajax")) {
          if (this.config.order !== null) {
            Object.assign(params, this.config.order);
          }
          if (this.config.filter !== null) {
            Object.assign(params, this.config.filter);
          }
          // Call ajax and render data
          data = await this.config.ajax(params);
          this.renderRows(data);
        } else {
          // Use data transmitted directly
          data = this.config.data;
          this.renderRows(data);
        }

        // Start listening to events of already initialized buttons
        this.attachActionEventListeners(".btn_edit");
        this.attachActionEventListeners(".btn_done");
      }

      // Get the current configuration of the table
      getConfig() {
        return this.config;
      }

      // Automatically merger new config and re-render
      updateConfig(newConfig) {
        this.config = {
          ...this.config,
          ...newConfig
        };
        this.render();
      }

      // Define and automatically create edit buttons
      // Used in rowRender configuration
      actionEdit(row) {
        return `
        <div class="d-flex" style="justify-content: space-evenly;">
          <i class="ri-edit-2-fill btn_edit btn btn-primary" style="--bs-btn-padding-y: .15rem; --bs-btn-padding-x: .3rem; --bs-btn-font-size: .75rem;" data-row-id="${row.id}"></i>
          <i class="ri-check-line btn_done btn btn-success" style="--bs-btn-padding-y: .15rem; --bs-btn-padding-x: .3rem; --bs-btn-font-size: .75rem; display: none;" data-row-id="${row.id}"></i>
        </div>
        `;
      }

      // Define and automatically create editable cells
      // Used in rowRender configuration
      cellEdit(colName, colValue) {
        return `
        <div class="cell_show"><span class="${colName}">${colValue}</span></div>
        <div class="cell_edit"><input class="${colName}" style="display: none;" type='text' value='${colValue}'/></div>
        `;
      }

      // Works when the edit button is clicked
      rowEdit(rowId) {
        this.config.heads.map((col) => {
          var cellShow = $(`#row_${rowId} .cell_show .${col.name}`);
          var cellEdit = $(`#row_${rowId} .cell_edit .${col.name}`);

          if (cellShow) {
            cellShow.hide();
          }

          if (cellEdit) {
            cellEdit.show();
          }
        });
        $(`#row_${rowId} div .btn_done`).show();
        $(`#row_${rowId} div .btn_edit`).hide();
      }

      rowEditDone(rowId) {
        // Initial params with row id
        let params = {
          id: parseInt(rowId)
        };

        // Get api update info
        let apiUpdateInfo = this.config.apiUpdate;

        // Add edited values ​​to params
        this.config.heads.map((col) => {
          var cellEdit = $(`#row_${rowId} .cell_edit .${col.name}`);

          if (cellEdit.val()) {
            Object.assign(params, {
              [col.name]: cellEdit.val()
            });
          }
        });
        // Call API update data row
        if (this.config.hasOwnProperty("apiUpdate")) {
          $.ajax({
            url: apiUpdateInfo.url,
            type: apiUpdateInfo.type,
            data: params,
            headers: apiUpdateInfo.headers,
            success: (response) => {
              // alert("Edit success");
              this.render();
            },
            error: function(error) {
              console.log(error);
              alert("Edit Fail");
            },
          });
        } else {
          alert("Missing updated API information");
        }

        //
        $(`#row_${rowId} div .btn_done`).hide();
        $(`#row_${rowId} div .btn_edit`).show();
      }

      // Update config and render when change per page
      changePerPage() {
        this.config.per_page = $(`${this.tableId} #perPageSelect`).val();
        this.render();
      };
    }

    var config = {
      // Declare configuration for table headers
      // 'render' function is used to customize the header
      heads: [{
          name: "id",
          value: "ID",
        },
        {
          name: "email",
          value: "Email",
        },
        {
          name: "code",
          value: "Code",
          render: (heads) => {
            var html = `
                    <h1>${heads.value}</h1>    
              `;
            return html;
          },
        },
        {
          name: "created_at",
          value: "Created at",
        },
        {
          name: "__actions",
          value: "Action",
        },
      ],

      /* data: data, */ //Use data if config.ajax not exist

      // Used to customize rows according to column names
      rowRender: (colName, colValue, row) => {
        switch (colName) {
          case "id":
            return `
          <div class="text-center">
            ${row.id}
          </div>
        `;
          case "email":
            return tableData.cellEdit(colName, colValue);
          case "code":
            return tableData.cellEdit(colName, colValue);
          case "__actions":
            return tableData.actionEdit(row);
          default:
            return colValue;
        }
      },
      // Ajax function to automatically get initial data and re-render times
      // If this parameter is not present, the library will use the default parameter "data" passed directly
      ajax: (params) => {
        return new Promise((resolve, reject) => {
          $.ajax({
            url: "https://payment.nswteam.net/api/v1/admin/discount/get",
            type: "GET",
            data: params,
            headers: {
              Authorization: "Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwczovL3BheW1lbnQubnN3dGVhbS5uZXQvYXBpL3YxL2FkbWluL2xvZ2luIiwiaWF0IjoxNzA4NDgxMDUwLCJleHAiOjE3MDg1Njc0NTAsIm5iZiI6MTcwODQ4MTA1MCwianRpIjoid0k3alN1OWxlZHJxM3IwSiIsInN1YiI6IjUiLCJwcnYiOiJkMmZmMjkzMzlhOGEzZTgyYzM1ODJhNWE4ZTczOWRmMTc4OWJiMTJmIn0.8aWr2DlfNvQZqBiXIFaDRS8_VsARAl1TEynsuYpZcqY", // Include the token in the request header
            },
            success: function(response) {
              resolve(response);
            },
            error: function(error) {
              console.log(error);
              reject(error);
            },
          });
        });
      },
      // Information used to call the API for the row and cell update function
      apiUpdate: {
        url: "https://payment.nswteam.net/api/v1/admin/discount/update",
        type: "POST",
        headers: {
          Authorization: "Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwczovL3BheW1lbnQubnN3dGVhbS5uZXQvYXBpL3YxL2FkbWluL2xvZ2luIiwiaWF0IjoxNzA4NDgxMDUwLCJleHAiOjE3MDg1Njc0NTAsIm5iZiI6MTcwODQ4MTA1MCwianRpIjoid0k3alN1OWxlZHJxM3IwSiIsInN1YiI6IjUiLCJwcnYiOiJkMmZmMjkzMzlhOGEzZTgyYzM1ODJhNWE4ZTczOWRmMTc4OWJiMTJmIn0.8aWr2DlfNvQZqBiXIFaDRS8_VsARAl1TEynsuYpZcqY", // Include the token in the request header
        },
      },
      footer: {
        pagination: true,
        detail: true
      },
      sort: {
        enable: true,
        listColumn: ["id", "email", "code", "created_at"]
      },
      order: {
        order_by_id: 'desc'
      }
    };

    // Initial table
    var tableData = new TableData("#bookTabe", config);
    tableData.render();

    // Get values from form html and append to config -> re-render with new data
    const getFormValues = () => {
      const formData = {};
      const form = document.getElementById("filterForm");

      for (let i = 0; i < form.elements.length; i++) {
        const element = form.elements[i];

        if (!element.name || element.tagName === "BUTTON") {
          continue;
        }

        formData[element.name] = element.value;
      }

      config.filter = formData;
      tableData.updateConfig(config);
    };

    // Search for keywords by line (tr tag)
    $("#search_input").on("keyup", function() {
      var value = $(this).val().toLowerCase();
      $("#bookTabe tbody tr").filter(function() {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
      });
    });
  </script>
</body>

</html>