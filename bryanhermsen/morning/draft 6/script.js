// Define the three sets of tasks
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

// Load the third column tasks from an external JSON file
let tasksColumn3 = [];
fetch('tasksColumn3.json')
  .then(response => response.json())
  .then(data => {
    tasksColumn3 = data;
  })
  .catch(error => console.error('Error loading tasks for column 3:', error));

const taskList = document.getElementById('taskList');

// Function to get Central Time
function getCentralTime() {
  const now = new Date();
  return new Date(now.toLocaleString('en-US', { timeZone: 'America/Chicago' }));
}

// Function to create task list with current column's tasks
function createTaskList(tasks) {
  taskList.innerHTML = ''; // Clear the previous list

  tasks.forEach((task, index) => {
    const taskDiv = document.createElement('div');
    taskDiv.className = 'task';
    
    const checkbox = document.createElement('input');
    checkbox.type = 'checkbox';
    checkbox.id = `task-${index}`;
    checkbox.addEventListener('change', () => updateTaskStatus(taskSpan, checkbox, task.time));

    const label = document.createElement('label');
    label.htmlFor = `task-${index}`;

    const taskSpan = document.createElement('span');
    taskSpan.textContent = task.time ? `${task.time} - ${task.description}` : task.description;
    
    updateTaskStatus(taskSpan, checkbox, task.time); // Set initial status based on time

    label.appendChild(taskSpan);
    taskDiv.appendChild(checkbox);
    taskDiv.appendChild(label);
    taskList.appendChild(taskDiv);
  });
}

// Update task status based on time and checkbox state
function updateTaskStatus(taskSpan, checkbox, time) {
  if (checkbox.checked) {
    taskSpan.className = 'completed';
  } else {
    const currentTime = getCentralTime();
    const taskTime = new Date();

    if (time) {
      const [hours, minutes] = time.split(':');
      taskTime.setHours(parseInt(hours), parseInt(minutes), 0);

      // Apply `.pending` if the task's time is in the future, otherwise `.active`
      taskSpan.className = taskTime > currentTime ? 'pending' : 'active';
    } else {
      taskSpan.className = 'pending';
    }
  }
}

// Radio button selection logic
document.querySelectorAll('input[name="taskColumn"]').forEach(radio => {
  radio.addEventListener('change', (event) => {
    switch (event.target.value) {
      case 'tasksColumn1':
        createTaskList(tasksColumn1);
        break;
      case 'tasksColumn2':
        createTaskList(tasksColumn2);
        break;
      case 'tasksColumn3':
        createTaskList(tasksColumn3);
        break;
    }
  });
});

// Load initial tasks
createTaskList(tasksColumn1);

// I added this, based on draft 3
// Update task statuses every minute
setInterval(updateTaskStatus, 60000);
