// Define the three sets of tasks
const tasksColumn1 = [
  { time: '21:00', description: 'Lights Out' },
  { time: '05:05', description: 'Alarm' },
  { time: '05:10', description: 'Stretch and read Gospel' },
  { time: '05:20', description: 'Crunches and sit-ups' },
  { time: '05:25', description: 'Make Coffee' },
  { time: '05:30', description: 'Make Oatmeal' },
  { time: '05:45', description: 'Start eating, Pack lunch(es), Pack bag' },
  { time: '06:00', description: 'Shower' },
  { time: '06:15', description: 'Shave, brush teeth' },
  { time: '06:30', description: 'Leave for the office' }
];

const tasksColumn2 = [
  { time: '21:00', description: 'Lights Out' },
  { time: '05:05', description: 'Alarm' },
  { time: '05:10', description: 'Stretch and read Gospel' },
  { time: '05:20', description: 'Crunches and sit-ups' },
  { time: '05:25', description: 'Make Coffee' },
  { time: '05:30', description: 'Make Oatmeal' },
  { time: '05:45', description: 'Eat breakfast' },
  { time: '06:05', description: 'Shower' },
  { time: '06:20', description: 'Shave, brush teeth' },
  { time: '06:40', description: 'Morning Prayer' },
  { time: '07:15', description: 'Start work' }
];

const tasksColumn3 = [
  { time: '21:00', description: 'Lights Out' },
  { time: '05:05', description: 'Alarm' },
  { time: '05:10', description: 'Stretch and read Gospel' },
  { time: '05:20', description: 'Crunches and sit-ups' },
  { time: '05:25', description: 'Make oatmeal' },
  { time: '05:45', description: 'Run or lift' },
  { time: '06:20', description: 'Shower' },
  { time: '06:35', description: 'Shave, brush teeth' },
  { time: '06:50', description: 'Eat, make coffee' },
  { time: '07:15', description: 'Start work' }
];

// Load the third column tasks from an external JSON file
let tasksColumn4 = [];
fetch('tasksColumn4.json')
  .then(response => response.json())
  .then(data => {
    tasksColumn4 = data;
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
      case 'tasksColumn4':
        createTaskList(tasksColumn4);
        break;
    }
  });
});

// Periodically update task statuses every minute -- wasn't working
/*
setInterval(() => {
  document.querySelectorAll('.task-list .task').forEach((taskDiv, index) => {
    const checkbox = taskDiv.querySelector('input[type="checkbox"]');
    const taskSpan = taskDiv.querySelector('span');
    const taskTime = currentTasks[index]?.time || ''; // Get time for the current task
    updateTaskStatus(taskSpan, checkbox, taskTime);
  });
}, 60000); // Check every 60,000 milliseconds (1 minute)
*/

// Periodically perform a function, proof of concept
/* setInterval(
    function () {console.log(getCentralTime())} ,
    60000);
*/
// Load initial tasks
createTaskList(tasksColumn1);
