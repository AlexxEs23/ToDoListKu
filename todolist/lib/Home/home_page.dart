import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'package:todolis/Auth/login.dart';
import 'package:todolis/settings.dart';

class HomePage extends StatefulWidget {
  const HomePage({Key? key}) : super(key: key);

  @override
  State<HomePage> createState() => _HomePageState();
}

class _HomePageState extends State<HomePage> {
  List<dynamic> _todos = [];
  bool _isLoading = true;
  String _username = "User";

  @override
  void initState() {
    super.initState();
    _fetchTodos();
    _getUserInfo();
  }

  Future<void> _getUserInfo() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final name = prefs.getString('username');
      if (name != null && name.isNotEmpty) {
        setState(() {
          _username = name;
        });
      }
    } catch (e) {
      // Silently handle error
    }
  }

  Future<void> _fetchTodos() async {
    setState(() {
      _isLoading = true;
    });

    try {
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('token');

      if (token == null) {
        _logout();
        return;
      }

      final response = await http.get(
        Uri.parse('$base_url/todos'),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );

      final responseData = jsonDecode(response.body);

      if (response.statusCode == 200) {
        setState(() {
          _todos = responseData['data'];
        });
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(responseData['message'] ?? 'Gagal mengambil data'),
            backgroundColor: Colors.red,
          ),
        );
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Terjadi kesalahan: $e'),
          backgroundColor: Colors.red,
        ),
      );
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  Future<void> _logout() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('token');
    if (!mounted) return;
    
    Navigator.of(context).pushReplacement(
      MaterialPageRoute(builder: (context) => const LoginPage()),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[100],
      body: SafeArea(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _buildHeader(),
            _buildTaskSummary(),
            const SizedBox(height: 20),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 20.0),
              child: Text(
                'My Tasks',
                style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                  color: Colors.grey[800],
                ),
              ),
            ),
            const SizedBox(height: 10),
            Expanded(
              child: _buildTaskList(),
            ),
          ],
        ),
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () {
          _showAddTaskDialog();
        },
        icon: const Icon(Icons.add),
        label: const Text('Add Task'),
        backgroundColor: Colors.blue,
      ),
    );
  }

  Widget _buildHeader() {
    return Container(
      padding: const EdgeInsets.all(20.0),
      decoration: BoxDecoration(
        color: Colors.white,
        boxShadow: [
          BoxShadow(
            color: Colors.grey.withOpacity(0.1),
            spreadRadius: 1,
            blurRadius: 5,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'Hello, $_username!',
                style: const TextStyle(
                  fontSize: 22,
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: 5),
              Text(
                'Let\'s organize your tasks',
                style: TextStyle(
                  fontSize: 14,
                  color: Colors.grey[600],
                ),
              ),
            ],
          ),
          Row(
            children: [
              IconButton(
                icon: const Icon(Icons.refresh),
                onPressed: _fetchTodos,
                tooltip: 'Refresh',
              ),
              IconButton(
                icon: const Icon(Icons.logout),
                onPressed: () {
                  _showLogoutConfirmation();
                },
                tooltip: 'Logout',
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildTaskSummary() {
    int totalTasks = _todos.length;
    int completedTasks = _todos.where((todo) => todo['status'] == 'selesai').length;
    int inProgressTasks = _todos.where((todo) => todo['status'] == 'proses').length;
    int pendingTasks = _todos.where((todo) => todo['status'] == 'belum_dikerjakan').length;

    return Container(
      margin: const EdgeInsets.all(20.0),
      child: Row(
        children: [
          _buildSummaryCard(
            'Total',
            totalTasks.toString(),
            Colors.blue,
            Icons.list_alt,
          ),
          _buildSummaryCard(
            'Completed',
            completedTasks.toString(),
            Colors.green,
            Icons.check_circle_outline,
          ),
          _buildSummaryCard(
            'In Progress',
            inProgressTasks.toString(),
            Colors.orange,
            Icons.pending_actions,
          ),
          _buildSummaryCard(
            'Pending',
            pendingTasks.toString(),
            Colors.red,
            Icons.hourglass_empty,
          ),
        ],
      ),
    );
  }

  Widget _buildSummaryCard(String title, String count, Color color, IconData icon) {
    return Expanded(
      child: Card(
        elevation: 2,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(12),
        ),
        child: Padding(
          padding: const EdgeInsets.all(12.0),
          child: Column(
            children: [
              Icon(
                icon,
                color: color,
                size: 24,
              ),
              const SizedBox(height: 8),
              Text(
                count,
                style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                  color: color,
                ),
              ),
              const SizedBox(height: 4),
              Text(
                title,
                style: TextStyle(
                  fontSize: 12,
                  color: Colors.grey[600],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildTaskList() {
    if (_isLoading) {
      return const Center(
        child: CircularProgressIndicator(),
      );
    }

    if (_todos.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.task_alt,
              size: 80,
              color: Colors.grey[400],
            ),
            const SizedBox(height: 16),
            Text(
              'No tasks yet',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
                color: Colors.grey[600],
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'Add a new task to get started',
              style: TextStyle(
                fontSize: 14,
                color: Colors.grey[500],
              ),
            ),
          ],
        ),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.symmetric(horizontal: 16.0),
      itemCount: _todos.length,
      itemBuilder: (context, index) {
        final todo = _todos[index];
        return _buildTaskCard(todo);
      },
    );
  }

  Widget _buildTaskCard(Map<String, dynamic> todo) {
    Color cardColor;
    IconData statusIcon;

    switch (todo['status']) {
      case 'belum_dikerjakan':
        cardColor = Colors.red.withOpacity(0.1);
        statusIcon = Icons.hourglass_empty;
        break;
      case 'proses':
        cardColor = Colors.orange.withOpacity(0.1);
        statusIcon = Icons.pending_actions;
        break;
      case 'selesai':
        cardColor = Colors.green.withOpacity(0.1);
        statusIcon = Icons.check_circle_outline;
        break;
      default:
        cardColor = Colors.grey.withOpacity(0.1);
        statusIcon = Icons.help_outline;
    }

    return Card(
      margin: const EdgeInsets.only(bottom: 16.0),
      elevation: 2,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
      ),
      child: Container(
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(16),
          border: Border.all(
            color: cardColor,
            width: 2,
          ),
        ),
        child: ListTile(
          contentPadding: const EdgeInsets.symmetric(
            horizontal: 16.0,
            vertical: 12.0,
          ),
          leading: CircleAvatar(
            backgroundColor: cardColor,
            child: Icon(
              statusIcon,
              color: _getStatusColor(todo['status']),
            ),
          ),
          title: Text(
            todo['judul_tugas'],
            style: const TextStyle(
              fontWeight: FontWeight.bold,
              fontSize: 16,
            ),
          ),
          subtitle: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const SizedBox(height: 4),
              Text(
                todo['deskripsi_tugas'],
                style: TextStyle(
                  color: Colors.grey[600],
                  fontSize: 14,
                ),
              ),
              const SizedBox(height: 8),
              _buildStatusChip(todo['status']),
            ],
          ),
          trailing: PopupMenuButton(
            icon: const Icon(Icons.more_vert),
            onSelected: (value) {
              // Handle menu actions
              if (value == 'edit') {
                _showEditTaskDialog(todo);
              } else if (value == 'delete') {
                _confirmDeleteTask(todo['id']);
              } else if (value == 'status') {
                _showChangeStatusDialog(todo);
              }
            },
            itemBuilder: (context) => [
              const PopupMenuItem(
                value: 'edit',
                child: Row(
                  children: [
                    Icon(Icons.edit, size: 18),
                    SizedBox(width: 8),
                    Text('Edit'),
                  ],
                ),
              ),
              const PopupMenuItem(
                value: 'status',
                child: Row(
                  children: [
                    Icon(Icons.refresh, size: 18),
                    SizedBox(width: 8),
                    Text('Change Status'),
                  ],
                ),
              ),
              const PopupMenuItem(
                value: 'delete',
                child: Row(
                  children: [
                    Icon(Icons.delete, size: 18, color: Colors.red),
                    SizedBox(width: 8),
                    Text('Delete', style: TextStyle(color: Colors.red)),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildStatusChip(String status) {
    Color chipColor = _getStatusColor(status);
    String statusText = _getStatusText(status);

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: chipColor.withOpacity(0.1),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: chipColor),
      ),
      child: Text(
        statusText,
        style: TextStyle(
          color: chipColor,
          fontSize: 12,
          fontWeight: FontWeight.bold,
        ),
      ),
    );
  }

  Color _getStatusColor(String status) {
    switch (status) {
      case 'belum_dikerjakan':
        return Colors.red;
      case 'proses':
        return Colors.orange;
      case 'selesai':
        return Colors.green;
      default:
        return Colors.grey;
    }
  }

  String _getStatusText(String status) {
    switch (status) {
      case 'belum_dikerjakan':
        return 'Pending';
      case 'proses':
        return 'In Progress';
      case 'selesai':
        return 'Completed';
      default:
        return 'Unknown';
    }
  }

  void _showLogoutConfirmation() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Logout'),
        content: const Text('Are you sure you want to logout?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              _logout();
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.red,
            ),
            child: const Text('Logout'),
          ),
        ],
      ),
    );
  }

  void _showAddTaskDialog() {
    final formKey = GlobalKey<FormState>();
    final TextEditingController titleController = TextEditingController();
    final TextEditingController descriptionController = TextEditingController();
    DateTime selectedDate = DateTime.now().add(const Duration(days: 1));
    String selectedStatus = 'belum_dikerjakan';

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Tambah Tugas Baru'),
        content: SingleChildScrollView(
          child: Form(
            key: formKey,
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                TextFormField(
                  controller: titleController,
                  decoration: const InputDecoration(
                    labelText: 'Judul Tugas',
                    border: OutlineInputBorder(),
                  ),
                  validator: (value) {
                    if (value == null || value.isEmpty) {
                      return 'Judul tugas tidak boleh kosong';
                    }
                    return null;
                  },
                ),
                const SizedBox(height: 16),
                TextFormField(
                  controller: descriptionController,
                  decoration: const InputDecoration(
                    labelText: 'Deskripsi Tugas',
                    border: OutlineInputBorder(),
                  ),
                  maxLines: 3,
                  validator: (value) {
                    if (value == null || value.isEmpty) {
                      return 'Deskripsi tugas tidak boleh kosong';
                    }
                    return null;
                  },
                ),
                const SizedBox(height: 16),
                Row(
                  children: [
                    const Text('Tanggal Selesai:'),
                    const SizedBox(width: 8),
                    Expanded(
                      child: StatefulBuilder(
                        builder: (context, setState) => OutlinedButton(
                          onPressed: () async {
                            final DateTime? picked = await showDatePicker(
                              context: context,
                              initialDate: selectedDate,
                              firstDate: DateTime.now(),
                              lastDate: DateTime.now().add(const Duration(days: 365)),
                            );
                            if (picked != null && picked != selectedDate) {
                              setState(() {
                                selectedDate = picked;
                              });
                            }
                          },
                          child: Text(
                            '${selectedDate.day}/${selectedDate.month}/${selectedDate.year}',
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 16),
                const Text('Status:'),
                StatefulBuilder(
                  builder: (context, setState) => Column(
                    children: [
                      RadioListTile<String>(
                        title: const Text('Belum Dikerjakan'),
                        value: 'belum_dikerjakan',
                        groupValue: selectedStatus,
                        onChanged: (value) {
                          setState(() {
                            selectedStatus = value!;
                          });
                        },
                      ),
                      RadioListTile<String>(
                        title: const Text('Dalam Proses'),
                        value: 'proses',
                        groupValue: selectedStatus,
                        onChanged: (value) {
                          setState(() {
                            selectedStatus = value!;
                          });
                        },
                      ),
                      RadioListTile<String>(
                        title: const Text('Selesai'),
                        value: 'selesai',
                        groupValue: selectedStatus,
                        onChanged: (value) {
                          setState(() {
                            selectedStatus = value!;
                          });
                        },
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            onPressed: () async {
              if (formKey.currentState!.validate()) {
                Navigator.pop(context);
                _addTask(
                  titleController.text,
                  descriptionController.text,
                  selectedDate,
                  selectedStatus,
                );
              }
            },
            child: const Text('Simpan'),
          ),
        ],
      ),
    );
  }

  Future<void> _addTask(
    String title,
    String description,
    DateTime dueDate,
    String status,
  ) async {
    try {
      setState(() {
        _isLoading = true;
      });

      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('token');

      if (token == null) {
        _logout();
        return;
      }

      final response = await http.post(
        Uri.parse('$base_url/post-todo'),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
        body: jsonEncode({
          'judul_tugas': title,
          'deskripsi_tugas': description,
          'tanggal_selesai': dueDate.toIso8601String().split('T')[0],
          'status': status,
        }),
      );

      final responseData = jsonDecode(response.body);

      if (response.statusCode == 200) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Tugas berhasil ditambahkan'),
            backgroundColor: Colors.green,
          ),
        );
        // Refresh todo list
        _fetchTodos();
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(responseData['message'] ?? 'Gagal menambahkan tugas'),
            backgroundColor: Colors.red,
          ),
        );
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Terjadi kesalahan: $e'),
          backgroundColor: Colors.red,
        ),
      );
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  void _confirmDeleteTask(int todoId) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Hapus Tugas'),
        content: const Text('Apakah Anda yakin ingin menghapus tugas ini?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              _deleteTask(todoId);
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.red,
            ),
            child: const Text('Hapus'),
          ),
        ],
      ),
    );
  }

  Future<void> _deleteTask(int todoId) async {
    try {
      setState(() {
        _isLoading = true;
      });

      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('token');

      if (token == null) {
        _logout();
        return;
      }

      final response = await http.delete(
        Uri.parse('$base_url/todos-delete/$todoId'),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );

      final responseData = jsonDecode(response.body);

      if (response.statusCode == 200) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Tugas berhasil dihapus'),
            backgroundColor: Colors.green,
          ),
        );
        // Refresh todo list
        _fetchTodos();
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(responseData['message'] ?? 'Gagal menghapus tugas'),
            backgroundColor: Colors.red,
          ),
        );
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Terjadi kesalahan: $e'),
          backgroundColor: Colors.red,
        ),
      );
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  void _showChangeStatusDialog(Map<String, dynamic> todo) {
    String selectedStatus = todo['status'];

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Ubah Status'),
        content: StatefulBuilder(
          builder: (context, setState) => Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              RadioListTile<String>(
                title: const Text('Belum Dikerjakan'),
                value: 'belum_dikerjakan',
                groupValue: selectedStatus,
                onChanged: (value) {
                  setState(() {
                    selectedStatus = value!;
                  });
                },
              ),
              RadioListTile<String>(
                title: const Text('Dalam Proses'),
                value: 'proses',
                groupValue: selectedStatus,
                onChanged: (value) {
                  setState(() {
                    selectedStatus = value!;
                  });
                },
              ),
              RadioListTile<String>(
                title: const Text('Selesai'),
                value: 'selesai',
                groupValue: selectedStatus,
                onChanged: (value) {
                  setState(() {
                    selectedStatus = value!;
                  });
                },
              ),
            ],
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              _updateTaskStatus(todo['id'], selectedStatus, todo);
            },
            child: const Text('Simpan'),
          ),
        ],
      ),
    );
  }

  Future<void> _updateTaskStatus(int todoId, String newStatus, Map<String, dynamic> todo) async {
    try {
      setState(() {
        _isLoading = true;
      });

      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('token');

      if (token == null) {
        _logout();
        return;
      }

      final response = await http.put(
        Uri.parse('$base_url/todos-update/$todoId'),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
        body: jsonEncode({
          'judul_tugas': todo['judul_tugas'],
          'deskripsi_tugas': todo['deskripsi_tugas'],
          'tanggal_selesai': todo['tanggal_selesai'],
          'status': newStatus,
        }),
      );

      final responseData = jsonDecode(response.body);

      if (response.statusCode == 200) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Status tugas berhasil diperbarui'),
            backgroundColor: Colors.green,
          ),
        );
        // Refresh todo list
        _fetchTodos();
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(responseData['message'] ?? 'Gagal memperbarui status tugas'),
            backgroundColor: Colors.red,
          ),
        );
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Terjadi kesalahan: $e'),
          backgroundColor: Colors.red,
        ),
      );
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  void _showEditTaskDialog(Map<String, dynamic> todo) {
    final formKey = GlobalKey<FormState>();
    final TextEditingController titleController = TextEditingController(text: todo['judul_tugas']);
    final TextEditingController descriptionController = TextEditingController(text: todo['deskripsi_tugas']);
    
    // Parse the date from database format (assuming it's in 'YYYY-MM-DD' format)
    DateTime selectedDate;
    try {
      selectedDate = DateTime.parse(todo['tanggal_selesai']);
    } catch (e) {
      selectedDate = DateTime.now().add(const Duration(days: 1));
    }
    
    String selectedStatus = todo['status'];

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Edit Tugas'),
        content: SingleChildScrollView(
          child: Form(
            key: formKey,
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                TextFormField(
                  controller: titleController,
                  decoration: const InputDecoration(
                    labelText: 'Judul Tugas',
                    border: OutlineInputBorder(),
                  ),
                  validator: (value) {
                    if (value == null || value.isEmpty) {
                      return 'Judul tugas tidak boleh kosong';
                    }
                    return null;
                  },
                ),
                const SizedBox(height: 16),
                TextFormField(
                  controller: descriptionController,
                  decoration: const InputDecoration(
                    labelText: 'Deskripsi Tugas',
                    border: OutlineInputBorder(),
                  ),
                  maxLines: 3,
                  validator: (value) {
                    if (value == null || value.isEmpty) {
                      return 'Deskripsi tugas tidak boleh kosong';
                    }
                    return null;
                  },
                ),
                const SizedBox(height: 16),
                Row(
                  children: [
                    const Text('Tanggal Selesai:'),
                    const SizedBox(width: 8),
                    Expanded(
                      child: StatefulBuilder(
                        builder: (context, setState) => OutlinedButton(
                          onPressed: () async {
                            final DateTime? picked = await showDatePicker(
                              context: context,
                              initialDate: selectedDate,
                              firstDate: DateTime.now(),
                              lastDate: DateTime.now().add(const Duration(days: 365)),
                            );
                            if (picked != null && picked != selectedDate) {
                              setState(() {
                                selectedDate = picked;
                              });
                            }
                          },
                          child: Text(
                            '${selectedDate.day}/${selectedDate.month}/${selectedDate.year}',
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 16),
                const Text('Status:'),
                StatefulBuilder(
                  builder: (context, setState) => Column(
                    children: [
                      RadioListTile<String>(
                        title: const Text('Belum Dikerjakan'),
                        value: 'belum_dikerjakan',
                        groupValue: selectedStatus,
                        onChanged: (value) {
                          setState(() {
                            selectedStatus = value!;
                          });
                        },
                      ),
                      RadioListTile<String>(
                        title: const Text('Dalam Proses'),
                        value: 'proses',
                        groupValue: selectedStatus,
                        onChanged: (value) {
                          setState(() {
                            selectedStatus = value!;
                          });
                        },
                      ),
                      RadioListTile<String>(
                        title: const Text('Selesai'),
                        value: 'selesai',
                        groupValue: selectedStatus,
                        onChanged: (value) {
                          setState(() {
                            selectedStatus = value!;
                          });
                        },
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            onPressed: () async {
              if (formKey.currentState!.validate()) {
                Navigator.pop(context);
                _editTask(
                  todo['id'],
                  titleController.text,
                  descriptionController.text,
                  selectedDate,
                  selectedStatus,
                );
              }
            },
            child: const Text('Simpan'),
          ),
        ],
      ),
    );
  }

  Future<void> _editTask(
    int todoId,
    String title,
    String description,
    DateTime dueDate,
    String status,
  ) async {
    try {
      setState(() {
        _isLoading = true;
      });

      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('token');

      if (token == null) {
        _logout();
        return;
      }

      final response = await http.put(
        Uri.parse('$base_url/todos-update/$todoId'),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
        body: jsonEncode({
          'judul_tugas': title,
          'deskripsi_tugas': description,
          'tanggal_selesai': dueDate.toIso8601String().split('T')[0],
          'status': status,
        }),
      );

      final responseData = jsonDecode(response.body);

      if (response.statusCode == 200) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Tugas berhasil diperbarui'),
            backgroundColor: Colors.green,
          ),
        );
        // Refresh todo list
        _fetchTodos();
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(responseData['message'] ?? 'Gagal memperbarui tugas'),
            backgroundColor: Colors.red,
          ),
        );
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Terjadi kesalahan: $e'),
          backgroundColor: Colors.red,
        ),
      );
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }
}
