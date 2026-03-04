function imageFormatter(value) {
    if (value) {
        // Handle array of images (for feature sections)
        if (Array.isArray(value)) {
            if (value.length === 0) {
                return '-';
            }
            var html = '';
            for (var i = 0; i < value.length; i++) {
                if (value[i]) {
                    html += '<a class="image-popup-no-margins one-image" href="' + value[i] + '">' +
                        '<img class="rounded avatar-md shadow img-fluid" alt="" src="' + value[i] + '" width="55">' +
                        '</a>';
                    if (i < value.length - 1) {
                        html += '<br>';
                    }
                }
            }
            return html;
        }
        // Handle single image (for other cases)
        return '<a class="image-popup-no-margins one-image" href="' + value + '">' +
            '<img class="rounded avatar-md shadow img-fluid" alt="" src="' + value + '" width="55">' +
            '</a>';
    } else {
        return '-';
    }
}

function videoFormatter(value) {
    if (value) {
        return '<div style="text-align: center;">' +
            '<a href="' + value + '" target="_blank" title="Play Video">' +
                '<i class="fas fa-video" style="font-size: 25px;"></i>' +
            '</a>' +
        '</div>';
    } else {
        return '<div style="text-align: center;">-</div>';
    }
}
// Detail formatter for Bootstrap Table
function detailFormatter(index, row) {
    var html = [];
    html.push('<div class="p-3">');
    html.push('<p><strong>ID:</strong> ' + row.id + '</p>');
    html.push('<p><strong>Name:</strong> ' + row.name + '</p>');
    html.push('<p><strong>Slug:</strong> ' + row.slug + '</p>');
    html.push('<p><strong>Description:</strong> ' + (row.description || 'N/A') + '</p>');
    html.push('<p><strong>Status:</strong> ' + row.status_formatted + '</p>');
    html.push('<p><strong>Created At:</strong> ' + row.created_at + '</p>');
    html.push('</div>');
    return html.join('');
}

function categoryNameFormatter(value, row) {
    let buttonHtml = '';
    if (row.subcategories_count > 0) {
        buttonHtml = `<button class="btn icon btn-xs btn-icon rounded-pill toggle-subcategories float-left btn-outline-primary text-center"
                            style="padding:.20rem; font-size:.875rem;cursor: pointer; margin-right: 5px;" data-id="${row.id}">
                        <i class="fa fa-plus"></i>
                      </button>`;
    } else {
        buttonHtml = `<span style="display:inline-block; width:30px;"></span>`;
    }
    return `${buttonHtml}${value}`;

}
function actionColumnFormatter(value, row, index)
{
  
    return '<div class="action-column-menu">'+ value +'</div>';
}

function subCategoryFormatter(value, row) {
    let url = `/category/${row.id}/subcategories`;
    return '<span> <div class="category_count">' + value + ' Sub Categories</div></span>';
}

function statusFormatter(value, row, index) {
    let checked = value == 1 || value == 'true' ? 'checked' : '';
    return `
        <div class="custom-control custom-switch custom-switch-2">
            <input type="checkbox" class="custom-control-input update-status" id="${row.id}" ${checked}>
            <label class="custom-control-label" for="${row.id}">&nbsp;</label>
        </div>
    `;
}



function subCategoryNameFormatter(value, row, level) {
    let dataLevel = 0;
    let indent = level * 35;
    let buttonHtml = '';
    if (row.subcategories_count > 0) {
        buttonHtml = `<button class="btn icon btn-xs btn-icon rounded-pill toggle-subcategories float-left btn-outline-primary text-center"
                            style="padding:.20rem; cursor: pointer; margin-right: 5px;" data-id="${row.id}" data-level="${dataLevel}">
                        <i class="fa fa-plus"></i>
                      </button>`;
    } else {
        buttonHtml = `<span style="display:inline-block; width:30px;"></span>`;
    }
    dataLevel += 1;
    return `<div style="padding-left:${indent}px;" class="justify-content-center">${buttonHtml}<span>${value}</span></div>`;

}

function customFieldFormatter(value, row) {
    let url = `/category/${row.id}/custom-fields`;
    return '<a href="' + url + '"> <div class="category_count">' + value + ' Custom Fields</div></a>';
}
function autoApproveItemSwitchFormatter(value, row) {
    return `<div class="form-check form-switch">
        <input class="form-check-input switch1 update-auto-approve-status" id="${row.id}" type="checkbox" role="switch" ${value ? 'checked' : ''}>
    </div>`;
}

function yesAndNoStatusFormatter(value,row,index) {
    if (value) {
        return "<span class='badge badge-success'>"+trans("Yes")+"</span>";
    } else {
        return "<span class='badge badge-danger'>"+trans("No")+ "</span>";
    }
}



function formFieldDefaultValuesFormatter(value, row,index) {
    let html = '';
    if (row.default_values && row.default_values.length) {
        html += '<ul>'
        $.each(row.default_values, function (index, value) {
            html += "<i class='fa fa-arrow-right' aria-hidden='true'></i> " + value + "<br>"
        });
    } else {
        html = '<div class="text-center">-</div>';
    }
    return html;
}

function yesAndNoFormatter(value) {
    if (value) {
        return '<span class="badge badge-success">Yes</span>';
    } else {
        return '<span class="badge badge-danger">No</span>';
    }
}


function capitalizeNameFormatter(value) {
    if (typeof value === 'string' && value.length > 0) {
        return value.charAt(0).toUpperCase() + value.slice(1);
    }
    return value; // or return ''; depending on your requirement
}

function sentenceCaseFormatter(value) {
    if (typeof value === 'string' && value.length > 0) {
        // Convert underscore-separated values to title case
        // e.g., "top_rated_courses" -> "Top Rated Courses"
        return value
            .split('_')
            .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
            .join(' ');
    }
    return value;
}

function courseLearningsFormatter(value, row) {
    let html = '';
    if(row.learnings && row.learnings.length > 0){
        html = '<ul>';
        $.each(row.learnings, function (index, item) {
            html += "<li>" + item.title + "</li>";
        });
        html += '</ul>';
    } else {
        html = '<div class="text-center">-</div>';
    }
    return html;
}

function courseRequirementsFormatter(value, row) {
    let html = '';
    if(row.requirements && row.requirements.length > 0){
        html = '<ul>';
        $.each(row.requirements, function (index, item) {
            html += "<li>" + item.requirement + "</li>";
        });
        html += '</ul>';
    } else {
        html = '<div class="text-center">-</div>';
    }
    return html;
}

function courseTagsFormatter(value, row) {
    let html = '';
    if(row.tags && row.tags.length > 0){
        html = '<ul>';
        $.each(row.tags, function (index, item) {
            html += "<li>" + item.tag + "</li>";
        });
        html += '</ul>';
    } else {
        html = '<div class="text-center">-</div>';
    }
    return html;
}

function courseChapterStatusFormatter(value, row) {
    let checked = row.status == true ? 'checked' : '';
    return `
        <div class="custom-control custom-switch custom-switch-2">
            <input type="checkbox" class="custom-control-input change-status-btn" id="${row.id}" data-id="${row.id}" data-type="${row.type}" data-isactive="${row.status}" ${checked}>
            <label class="custom-control-label" for="${row.id}">&nbsp;</label>
        </div>
    `;
}

function viewDetailsFormatter(value, row) {
    return `<a href='' class='btn icon btn-xs btn-rounded btn-icon rounded-pill btn-info view-details-btn' title='Add Curriculum' data-toggle='modal' data-target='#viewDetailsModal' data-id="${row.id}" data-type="${row.type}" data-url="${row.particular_details_url}"><i class='fas fa-eye'></i></a>`;
}
