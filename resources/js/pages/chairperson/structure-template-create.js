/**
 * Chairperson Structure Template Create Page JavaScript
 * Handles dynamic component builder with weight validation
 */

export function initStructureTemplateCreatePage() {
  const templateNameInput = document.getElementById('template_name');
  const labelInput = document.getElementById('label');
  const componentsContainer = document.getElementById('components-container');
  const addComponentBtn = document.getElementById('add-component-btn');
  const form = document.getElementById('templateRequestForm');
  const submitBtn = document.getElementById('submit-btn');
  const weightTotalSpan = document.getElementById('weight-total');
  const weightIndicator = document.getElementById('weight-indicator');
  const weightAlert = document.getElementById('weight-alert');
  const weightMessage = document.getElementById('weight-message');

  if (!componentsContainer || !form) return;

  let componentIdCounter = 1;
  let subComponentIdCounter = 1;

  function syncMainComponentMaxState(componentElement) {
    if (!componentElement) {
      return;
    }

    const maxInput = componentElement.querySelector('.component-max-items');
    const helperText = componentElement.querySelector('.component-max-helper');
    const hasSubComponents = componentElement.querySelectorAll('.subcomponent-item').length > 0;

    if (!maxInput) {
      return;
    }

    if (hasSubComponents) {
      maxInput.value = '';
      maxInput.disabled = true;
      maxInput.classList.add('bg-light');
      if (helperText) {
        helperText.textContent = 'Disabled when sub-components exist';
      }
    } else {
      maxInput.disabled = false;
      maxInput.classList.remove('bg-light');
      if (helperText) {
        helperText.textContent = 'Limit: 1-5';
      }
    }
  }

  // Auto-generate label from template name
  if (templateNameInput && labelInput) {
    templateNameInput.addEventListener('input', () => {
      labelInput.value = templateNameInput.value;
    });
  }

  // Initialize with one component
  addComponent();

  if (addComponentBtn) {
    addComponentBtn.addEventListener('click', () => addComponent());
  }

  function addComponent() {
    const template = document.getElementById('component-template');
    if (!template) return;

    const clone = template.content.cloneNode(true);
    const componentId = 'comp_' + componentIdCounter++;

    const componentItem = clone.querySelector('.component-item');
    componentItem.dataset.componentId = componentId;

    const removeBtn = clone.querySelector('.remove-component-btn');
    removeBtn.addEventListener('click', () => {
      componentItem.remove();
      updateWeightTotal();
    });

    const addSubBtn = clone.querySelector('.add-subcomponent-btn');
    addSubBtn.addEventListener('click', () => addSubComponent(componentItem, componentId));

    // Add weight change listener
    const weightInput = clone.querySelector('.component-weight');
    weightInput.addEventListener('input', updateWeightTotal);

    // Auto-generate label from name unless user edits label
    const nameInput = clone.querySelector('.activity-name-input');
    const labelInputLocal = clone.querySelector('.component-label');
    if (nameInput && labelInputLocal) {
      // initialize data-auto if not present
      if (typeof labelInputLocal.dataset.auto === 'undefined') {
        labelInputLocal.dataset.auto = 'true';
      }

      const toTitleCase = (str) => {
        return String(str).replace(/\w\S*/g, (txt) => txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase());
      };

      nameInput.addEventListener('input', () => {
        if (labelInputLocal.dataset.auto !== 'false') {
          labelInputLocal.value = toTitleCase(nameInput.value.trim());
        }
      });

      labelInputLocal.addEventListener('input', () => {
        // if user types into label, stop auto-updating
        labelInputLocal.dataset.auto = 'false';
      });
    }

    componentsContainer.appendChild(clone);
    syncMainComponentMaxState(componentItem);
    updateWeightTotal();
  }

  function addSubComponent(parentElement, parentId) {
    const template = document.getElementById('subcomponent-template');
    if (!template) return;

    const clone = template.content.cloneNode(true);
    const subId = 'sub_' + subComponentIdCounter++;

    const subItem = clone.querySelector('.subcomponent-item');
    subItem.dataset.subcomponentId = subId;
    subItem.dataset.parentId = parentId;

    const removeBtn = clone.querySelector('.remove-subcomponent-btn');
    removeBtn.addEventListener('click', () => {
      subItem.remove();
      syncMainComponentMaxState(parentElement);
      updateWeightTotal();
    });

    // Add weight change listener
    const weightInput = clone.querySelector('.component-weight');
    weightInput.addEventListener('input', updateWeightTotal);

    // Auto-generate label from name for subcomponent
    const nameInput = clone.querySelector('.activity-name-input');
    const labelInputLocal = clone.querySelector('.component-label');
    if (nameInput && labelInputLocal) {
      if (typeof labelInputLocal.dataset.auto === 'undefined') {
        labelInputLocal.dataset.auto = 'true';
      }

      const toTitleCase = (str) => {
        return String(str).replace(/\w\S*/g, (txt) => txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase());
      };

      nameInput.addEventListener('input', () => {
        if (labelInputLocal.dataset.auto !== 'false') {
          labelInputLocal.value = toTitleCase(nameInput.value.trim());
        }
      });

      labelInputLocal.addEventListener('input', () => {
        labelInputLocal.dataset.auto = 'false';
      });
    }

    const subContainer = parentElement.querySelector('.subcomponents-container');
    subContainer.appendChild(clone);
    syncMainComponentMaxState(parentElement);
    updateWeightTotal();
  }

  function updateWeightTotal() {
    let total = 0;
    let hasSubComponentError = false;
    const mainComponents = componentsContainer.querySelectorAll('.component-item');

    mainComponents.forEach((comp) => {
      const weight = parseFloat(comp.querySelector('.component-weight').value) || 0;
      total += weight;

      // Check sub-component weights separately
      const subs = comp.querySelectorAll('.subcomponent-item');
      const subWeightIndicator = comp.querySelector('.sub-weight-indicator');
      const subWeightTotalSpan = comp.querySelector('.sub-weight-total');

      if (subs.length > 0) {
        let subTotal = 0;
        subs.forEach((sub) => {
          const subWeight = parseFloat(sub.querySelector('.component-weight').value) || 0;
          subTotal += subWeight;
        });

        // Show sub-component indicator
        if (subWeightIndicator && subWeightTotalSpan) {
          subWeightIndicator.classList.remove('d-none');
          subWeightTotalSpan.textContent = subTotal.toFixed(2);

          // Color code the sub-component indicator
          if (subTotal === 100) {
            subWeightTotalSpan.classList.remove('text-danger', 'text-warning');
            subWeightTotalSpan.classList.add('text-success');
          } else {
            subWeightTotalSpan.classList.remove('text-success');
            if (subTotal > 100) {
              subWeightTotalSpan.classList.remove('text-warning');
              subWeightTotalSpan.classList.add('text-danger');
            } else {
              subWeightTotalSpan.classList.remove('text-danger');
              subWeightTotalSpan.classList.add('text-warning');
            }
            hasSubComponentError = true;
          }
        }
      } else {
        // Hide indicator if no sub-components
        if (subWeightIndicator) {
          subWeightIndicator.classList.add('d-none');
        }
      }
    });

    // Update main display
    if (weightTotalSpan) {
      weightTotalSpan.textContent = total.toFixed(2);
    }

    // Update main indicator badge
    const totalValid = total === 100;
    const allSubComponentsValid = !hasSubComponentError;

    if (totalValid && allSubComponentsValid) {
      if (weightIndicator) {
        weightIndicator.classList.remove('bg-secondary', 'bg-danger', 'bg-warning');
        weightIndicator.classList.add('bg-success');
      }
      if (weightAlert) {
        weightAlert.classList.add('d-none');
      }
      if (submitBtn) {
        submitBtn.disabled = false;
      }
    } else {
      if (submitBtn) {
        submitBtn.disabled = true;
      }

      if (!totalValid) {
        if (total > 100) {
          if (weightIndicator) {
            weightIndicator.classList.remove('bg-secondary', 'bg-success', 'bg-warning');
            weightIndicator.classList.add('bg-danger');
          }
          if (weightAlert) {
            weightAlert.classList.remove('d-none');
          }
          if (weightMessage) {
            weightMessage.textContent = `Main component weights total ${total.toFixed(2)}% (exceeds 100%). Please adjust.`;
          }
        } else {
          if (weightIndicator) {
            weightIndicator.classList.remove('bg-success', 'bg-danger', 'bg-warning');
            weightIndicator.classList.add('bg-secondary');
          }
          if (weightAlert) {
            weightAlert.classList.remove('d-none');
          }
          if (weightMessage) {
            weightMessage.textContent = `Main component weights total ${total.toFixed(2)}% (must be exactly 100%). Please add ${(100 - total).toFixed(2)}% more.`;
          }
        }
      } else if (hasSubComponentError) {
        if (weightIndicator) {
          weightIndicator.classList.remove('bg-secondary', 'bg-danger');
          weightIndicator.classList.add('bg-warning');
        }
        if (weightAlert) {
          weightAlert.classList.remove('d-none');
        }
        if (weightMessage) {
          weightMessage.textContent = `Main components are correct, but some sub-components don't total 100%. Check each component's sub-component weights.`;
        }
      }
    }
  }

  form.addEventListener('submit', (e) => {
    e.preventDefault();

    // Validate weights first
    let total = 0;
    let hasSubComponentError = false;
    const mainComponents = componentsContainer.querySelectorAll('.component-item');

    mainComponents.forEach((comp) => {
      const weight = parseFloat(comp.querySelector('.component-weight').value) || 0;
      total += weight;

      // Check sub-components
      const subs = comp.querySelectorAll('.subcomponent-item');
      if (subs.length > 0) {
        let subTotal = 0;
        subs.forEach((sub) => {
          const subWeight = parseFloat(sub.querySelector('.component-weight').value) || 0;
          subTotal += subWeight;
        });

        if (subTotal !== 100) {
          hasSubComponentError = true;
        }
      }
    });

    if (total !== 100) {
      alert(`Main component weights must total exactly 100%. Current total: ${total.toFixed(2)}%`);
      return;
    }

    if (hasSubComponentError) {
      alert(
        'Each component with sub-components must have sub-component weights totaling exactly 100%. Please check your sub-component weights.'
      );
      return;
    }

    const structure = [];

    mainComponents.forEach((comp) => {
      const componentId = comp.dataset.componentId;
      const activityType = comp.querySelector('.activity-name-input').value.trim();
      const label = comp.querySelector('.component-label').value.trim();
      const weight = parseFloat(comp.querySelector('.component-weight').value);
      const maxItemsInput = comp.querySelector('.component-max-items');
      const maxItems = maxItemsInput && maxItemsInput.value ? parseInt(maxItemsInput.value) : null;

      if (label && !isNaN(weight)) {
        structure.push({
          component_id: componentId,
          activity_type: activityType,
          label: label,
          weight: weight,
          max_items: maxItems,
          is_main: true,
          parent_id: null,
        });

        const subs = comp.querySelectorAll('.subcomponent-item');
        subs.forEach((sub) => {
          const subId = sub.dataset.subcomponentId;
          const subActivityType = sub.querySelector('.activity-name-input').value.trim();
          const subLabel = sub.querySelector('.component-label').value.trim();
          const subWeight = parseFloat(sub.querySelector('.component-weight').value);
          const subMaxItemsInput = sub.querySelector('.component-max-items');
          const subMaxItems = subMaxItemsInput && subMaxItemsInput.value ? parseInt(subMaxItemsInput.value) : null;

          if (subLabel && !isNaN(subWeight)) {
            structure.push({
              subcomponent_id: subId,
              activity_type: subActivityType,
              label: subLabel,
              weight: subWeight,
              max_items: subMaxItems,
              is_main: false,
              parent_id: componentId,
            });
          }
        });
      }
    });

    // Add structure to form as hidden input
    const structureInput = document.createElement('input');
    structureInput.type = 'hidden';
    structureInput.name = 'structure';
    structureInput.value = JSON.stringify(structure);
    form.appendChild(structureInput);

    form.submit();
  });
}

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function () {
  if (
    document.querySelector('[data-page="chairperson-structure-template-create"]') ||
    document.getElementById('components-container')
  ) {
    initStructureTemplateCreatePage();
  }
});

window.initStructureTemplateCreatePage = initStructureTemplateCreatePage;
