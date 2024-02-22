class TableData {
  config = {
    order: null,
    filter: null,
    heads: [],
    page: 1,
    per_page: 15,
    data: {},
    rowRender: (colName, colValue, row) => {
      return colValue;
    },
    footer: {
      pagination: false,
      detail: false,
    },
    sort: {
      enable: false,
      listColumn: [],
    },
    positionCaption: "top"
  };

  constructor(tableId, config) {
    this.config = {
      ...this.config,
      ...config,
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
      //   this.attachSortEventListeners(".ri-expand-up-down-fill");
    } else {
      if (this.config.order === null) {
        this.config.order = {
          [`order_by_${column}`]: "desc",
        };
        this.updateConfig(this.config);
        // this.attachSortEventListeners(".ri-arrow-down-s-fill");
      } else {
        this.config.order = {
          [`order_by_${column}`]: "asc",
        };
        this.updateConfig(this.config);
        // this.attachSortEventListeners(".ri-arrow-up-s-fill");
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
    const elements = document.querySelectorAll(
      `${this.tableId} #perpage div #perPageSelect`
    );
    elements[0].addEventListener("change", (event) => {
      const value = event.target.value;
      this.config.per_page = value;
      this.render();
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
                    ${
                      this.config.sort.enable &&
                      this.config.sort.listColumn &&
                      this.config.sort.listColumn.includes(head.name)
                        ? this.renderIconSort(head.name)
                        : ""
                    }
                  </div>
                </th>`;
        } else {
          ths += `<th scope="col">
                  <div class="d-flex justify-content-center align-items-center">
                    ${head.value}
                    ${
                      this.config.sort.enable &&
                      this.config.sort.listColumn &&
                      this.config.sort.listColumn.includes(head.name)
                        ? this.renderIconSort(head.name)
                        : ""
                    }
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
        html += `<td ${
          col.name !== "__actions" ? `title="${row[col.name]}"` : ""
        }>${this.config.rowRender(col.name, row[col.name], row)}</td>`;
      });
      html += "</tr>";
    });
    html += "</tbody>";
    // Initial pagination use 'data.links'
    let htmlPagination = `<ul class="pagination m-0">`;
    data.links.map((link) => {
      htmlPagination += `
        <li class="page-item ${link.active ? "active" : ""}" style="width: 30px; height: 30px;">
          <span class="page_number page-link d-flex align-items-center justify-content-center" data-column-name="${
            link.url !== null ? link.url.split("=")[1] : null
          }" style="cursor: pointer; width: 30px; height: 30px;">${link.label
        .replace(" Previous", "")
        .replace("Next ", "")}</span>
      </li>
    `;
    });
    htmlPagination += "</ul>";
    html += `<caption style="caption-side: ${this.config.positionCaption}">
              <div class="d-flex align-items-center" style="justify-content: space-between;">`;
    html += `<div id="perpage">
              <!-- Per page -->
                <div class="d-flex align-items-center form-inline">
                  <label class="mr-2">Show</label>
                  <select id="perPageSelect" class="form-select form-select-sm me-2" style="font-size: 0.8rem; margin: 0 5px; cursor: pointer;">
                    <option value="15" ${
                      this.config.per_page.toString() === "15" ? "selected" : ""
                    }>15</option>
                    <option value="30" ${
                      this.config.per_page.toString() === "30" ? "selected" : ""
                    }>30</option>
                    <option value="50" ${
                      this.config.per_page.toString() === "50" ? "selected" : ""
                    }>50</option>
                    <option value="80" ${
                      this.config.per_page.toString() === "80" ? "selected" : ""
                    }>80</option>
                    <option value="100" ${
                      this.config.per_page.toString() === "100"
                        ? "selected"
                        : ""
                    }>100</option>
                  </select>
                  <label style="width: auto;">entries</label>
                </div>
              <!-- Per page -->
              </div>`;
    if (this.config.footer.detail) {
      html += `<div id="footer_detail">Show ${data.from} to ${data.to} of ${data.total} entries</div>`;
    }

    if (this.config.footer.pagination) {
      html += `<div id="footer_pagination">${htmlPagination}</div>`;
    }

    html += `</div></caption>`;
    $(this.tableId).append(html);
    this.attachPaginationEventListeners();
    this.attachPerPageEventListeners();
  }

  // Render table
  async render() {
    let data;
    let params = {
      per_page: this.config.per_page,
      page: this.config.page,
    };
    let className = "";
    // Clear html
    $(this.tableId).html("");
    // Initial headers
    $(this.tableId).append(this.renderHeads());
    if (this.config.order === null) {
      className = ".ri-expand-up-down-fill";
    } else {
      if (Object.values(this.config.order)[0] === "asc") {
        className = ".ri-arrow-up-s-fill";
      } else {
        className = ".ri-arrow-down-s-fill";
      }
    }
    this.attachSortEventListeners(className);

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
      ...newConfig,
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
      id: parseInt(rowId),
    };

    // Get api update info
    let apiUpdateInfo = this.config.apiUpdate;

    // Add edited values ​​to params
    this.config.heads.map((col) => {
      var cellEdit = $(`#row_${rowId} .cell_edit .${col.name}`);

      if (cellEdit.val()) {
        Object.assign(params, {
          [col.name]: cellEdit.val(),
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
          this.notification(`Row update successful`, "success");
          this.render();
        },
        error: function (error) {
          console.log(error);
          this.notification(error, "danger");
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
  }

  // Toast
  notification(message, theme) {
    var elementToRemove = document.querySelector(".toasts");
    if (elementToRemove) {
      elementToRemove.remove();
    }

    var div = document.createElement("div");
    div.classList.add("toasts");
    div.innerHTML = `
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="liveToast" class="toast text-bg-${theme} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header border-0">
                <strong class="me-auto text-${theme}">Success</strong>
                <small>now</small>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body border-0">
                ${message}
            </div>
        </div>
    </div>
`;
    document.body.appendChild(div);
    const toastLiveExample = document.getElementById("liveToast");
    const toast = new bootstrap.Toast(toastLiveExample);
    toast.show();
  }
}
