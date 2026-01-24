// Define two sets of tasks directly, and a third set to be loaded from a file
const tasksColumn1 = [
  { time: '04:59', description: 'Before 5:05 - Sleep, stretch, or make coffee' },
  { time: '05:05', description: 'Alarm' },
  { time: '05:10', description: 'Flex room - Read a reading, prayer routine' },
  { time: '05:20', description: 'Duolingo, stretch' },
  { time: '05:20', description: 'Crunches and sit-ups' },
  { time: '05:20', description: 'Make coffee' },
  { time: '05:30', description: 'Shower' },
  { time: '05:40', description: 'Shave, brush teeth' },
  { time: '05:55', description: 'Make oatmeal' },
  { time: '06:05', description: 'Eat' },
  { time: '06:15', description: 'Pack lunch(es)' },
  { time: '06:20', description: 'Pack bag' },
  { time: '06:30', description: 'Leave for the office' }
];

const tasksColumn2 = [
  { time: '05:00', description: 'Before 5:00, sleep, stretch, or make coffee' },
  { time: '05:05', description: 'Alarm' },
  { time: '05:10', description: 'Flex room - Read a reading, prayer routine' },
  { time: '05:20', description: 'Duolingo, stretch' },
  { time: '05:20', description: 'Crunches and sit-ups' },
  { time: '05:30', description: 'Run or lift' },
  { time: '06:00', description: 'Make oatmeal' },
  { time: '06:10', description: 'Eat, make coffee' },
  { time: '06:30', description: 'Shower' },
  { time: '06:40', description: 'Shave, brush teeth' },
  { time: '07:00', description: 'Start work' }
];

const taskList1 = document.getElementById('taskList1');
const taskList2 = document.getElementById('taskList2');
const taskList3 = document.getElementById('taskList3');

// Load the third column tasks from an external JSON file
fetch('tasksColumn3.json')
  .then(response => response.json())
  .then(data => {
    createTaskList(taskList3, data, 3);
  })
  .catch(error => console.error('Error loading tasks for column 3:', error));

// Helper function to get Central Time (CT)
function getCentralTime() {
  const now = new Date();
  return new Date(now.toLocaleString('en-US', { timeZone: 'America/Chicago' }));
}

// Function to create a task list with checkboxes
function createTaskList(taskListElement, tasks, columnIndex) {
  tasks.forEach((task, index) => {
    const taskDiv = document.createElement('div');
    taskDiv.className = 'task';
    
    const checkbox = document.createElement('input');
    checkbox.type = 'checkbox';
    checkbox.id = `task-${columnIndex}-${index}`;
    checkbox.classList.add('hidden-checkbox');
    checkbox.addEventListener('change', () => updateTaskStatus(taskSpan, checkbox, task.time));

    const label = document.createElement('label');
    label.htmlFor = `task-${columnIndex}-${index}`;

    const taskSpan = document.createElement('span');
    taskSpan.textContent = task.time ? `${task.time} - ${task.description}` : task.description;
    taskSpan.classList.add('pending');
    
    label.appendChild(taskSpan);
    taskDiv.appendChild(checkbox);
    taskDiv.appendChild(label);
    taskListElement.appendChild(taskDiv);
  });
}

// Create initial task lists
createTaskList(taskList1, tasksColumn1, 1);
createTaskList(taskList2, tasksColumn2, 2);

// Update the task style based on time and checkbox state
function updateTaskStatus(taskSpan, checkbox, time) {
  if (checkbox.checked) {
    taskSpan.className = 'completed';
  } else {
    const currentTime = getCentralTime();
    const taskTime = new Date();
    if (time) {
      const [hours, minutes] = time.split(':');
      taskTime.setHours(parseInt(hours), parseInt(minutes), 0);
      taskSpan.className = taskTime <= currentTime ? 'active' : 'pending';
    } else {
      taskSpan.className = 'pending';
    }
  }
}

// Update displayed column based on selected radio button
function updateColumnDisplay(selectedColumn) {
  document.querySelectorAll('.task-list').forEach((taskList, index) => {
    const isSelected = selectedColumn === `column${index + 1}`;
    taskList.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
      checkbox.classList.toggle('hidden-checkbox', !isSelected);
    });
    taskList.querySelectorAll('span').forEach(taskSpan => {
      taskSpan.className = isSelected ? taskSpan.className.replace('pending', 'active') : 'pending';
    });
  });
}

// Set up the column selector
document.querySelectorAll('input[name="column"]').forEach(radio => {
  radio.addEventListener('change', (event) => {
    updateColumnDisplay(event.target.value);
  });
});

// Initial column setup
updateColumnDisplay('column1');
