module.exports = {
  apps : [
  {
    name   : "laravel",
    script : "artisan",
    args:["queue:work"],
    exec_interpreter:'php',

  },
  {
    name   : "laravel2",
    script : "artisan",
    args:["queue:work"],
    exec_interpreter:'php',
    
  }
  ]
}
