# w3schools python tutorial, https://www.w3schools.com/python/default.asp

print("Hello, World!")

# Check Python version
import sys
print(sys.version)

# Get Started, https://www.w3schools.com/python/python_getstarted.asp
# Terminal: to use python in a Terminal window, start with "python3"
    # python3
    # print("Hello, World!")

# Syntax, https://www.w3schools.com/python/python_syntax.asp
# If loop structure and indendtation
if 5 > 2:
  print("Five is greater than two!")

# Variables are created when you assign a value
x = 5
y = "Hello, world" #Comments can come after commands; everything after hash is a comment

"""Comments
Multi-line strings are placed in triple quotes. Python ignores strings that are not assigned to a variable, 
so a multi-line string works as a multi-line comment.
Or just put a hash at the beginning of each comment line.
"""

# Variables: variables are not declared with any particular type and can change type after being set
print(x)
print(y)
x = "Variables"
print(x)

# Casting (optional)
x = str(3)
y = int(3)
z = float(3)

# Get type
print("Variable types:")
print(type(x))
print(type(y))
print(type(z))

# Multiple values
print("Multiple values, y:")
x, y, z = "Orange", "Banana", "Cherry"
print(y)
x = y = z = "Orange"
print(y)

# Unpack a collection
fruits = ["apple", "banana", "cherry"]
x, y, z = fruits

# Output variables, https://www.w3schools.com/python/python_variables_output.asp
# print with comma
x="print"
y="with"
z="commas"
print(x, y, z)

# print with plus
z="plus"
print(x + y + z)

# Variables outside a function are global. 
# Variables inside a function are local unless declared using the global keyword.
x = "awesome"
y = "apple"

def myfunc():
  x = "fantastic"
  print("Inside function: Python is " + x)

  global y
  y = "banana"

myfunc()

print("Outside function: Python is " + x) 
print(y)

# Python Strings, https://www.w3schools.com/python/python_strings.asp
print("Strings")
# Strings are arrays. There is no char type; a letter is an array len 1
a = "Hello, World!"
print(a[1]) # Indexing is 0-based
print(a[1:3]) # Slicing; prints 2nd and 3rd letter, not 4th
print(a[:5]) # Slicing from the start
print(a[7:]) # Slice to the end
print(a[-5:-2]) # Negative Indexing
print(len(a)) # Length of a string

# Loop through elements of a string
for x in "banana":
  print(x)

# Search a string with "in"
txt = "The best things in life are free!"
print("free" in txt)
print("expensive" in txt)
print("expensive" not in txt)

# Concatenate strings
print("string 1" + " " + "string 2")