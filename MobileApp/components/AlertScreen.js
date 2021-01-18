import React, { Component } from 'react'
import { Text, View, ScrollView, StyleSheet, StatusBar, TouchableOpacity } from 'react-native'
import { acceptAlert, cancelAlert, completeAlert } from '../api/alerts.js'

class AlertScreen extends Component {
  state = {
    alert: {}
  }
  
  const = this.state.alert = this.props.route.params.alert;

  onPressComplete = () => {
    completeAlert(this.state.alert.id)
      .then(() => {
        this.setState({ alert: { ...this.state.alert, status: 'completed'} });
      })
      .catch((err) => console.log(err));
  }

  onPressCancel = () => {
    cancelAlert(this.state.alert.id)
      .then(() => {
        this.setState({ alert: { ...this.state.alert, status: 'cancelled'} });
      })
      .catch((err) => console.log(err));
  }

  onPressAccept = () => {
    acceptAlert(this.state.alert.id)
      .then(() => {
        this.setState({ alert: { ...this.state.alert, status: 'accepted'} });
      })
      .catch((err) => console.log(err));
  }

  render() {
    let buttons;

    if (this.state.alert.status === 'raised') {
      buttons = <View style={styles.buttonContainer}>
          <View style={styles.buttonView}>
            <TouchableOpacity
              style={styles.button}
              onPress={() => this.onPressAccept()}
              underlayColor='#fff'>
              <Text style={styles.actionText}>Accept</Text>
            </TouchableOpacity>
          </View>
          <View style={styles.buttonView}>
            <TouchableOpacity
              style={styles.button}
              onPress={() => this.onPressCancel()}
              underlayColor='#fff'>
              <Text style={styles.actionText}>Cancel</Text>
            </TouchableOpacity>
          </View>
        </View>
    } else if (this.state.alert.status === 'accepted') {
      buttons = <View style={styles.buttonContainer}>
          <View style={styles.buttonView}>
            <TouchableOpacity
              style={styles.button}
              onPress={() => this.onPressComplete()}
              underlayColor='#fff'>
              <Text style={styles.actionText}>Complete</Text>
            </TouchableOpacity>
          </View>
          <View style={styles.buttonView}>
            <TouchableOpacity
              style={styles.button}
              onPress={() => this.onPressCancel()}
              underlayColor='#fff'>
              <Text style={styles.actionText}>Cancel</Text>
            </TouchableOpacity>
          </View>
        </View>
    }

    return (
      <View style={styles.item}>
        <View style={styles.header}>
          <Text style={styles.title}>
            {this.state.alert.title}
          </Text>
        </View>
        <View style={styles.body}>
          <Text>
            Date Raised: {this.state.alert.raisedDate}
          </Text>
          <Text>
            Assignee: {this.state.alert.assigned !== '' ? this.state.alert.assigned : 'Unassigned'}
          </Text>
          <Text style={styles.incidentId}>
            Incident ID: #{this.state.alert.incidentId}
          </Text>
          <Text style={styles.status}>
            Status: {this.state.alert.status}
          </Text>
        </View>
        {buttons}
        <View style={styles.scrollView}>
          <ScrollView>
            <Text style={styles.text}>
              {this.state.alert.description}
            </Text>
          </ScrollView>
        </View>
      </View>
    );
  }
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    marginTop: StatusBar.currentHeight || 0,
  },
  header: {
    backgroundColor: '#03A5C9',
    padding: 10,
  },
  body: {
    padding: 10,
  },
  item: {
    paddingBottom: 10,
  },
  title: {
    fontSize: 24,
  },
  buttonContainer: {
    flex: 1,
    flexDirection: "row",
    alignItems: 'center',
    justifyContent: 'center',
  },
  buttonView: {
    flex: 1,
    height: 10
  },
  button: {
    marginRight: 40,
    marginLeft: 40,
    marginTop: 10,
    paddingTop: 10,
    paddingBottom: 10,
    backgroundColor: '#1E6738',
    borderRadius: 10,
    borderWidth: 1,
    borderColor: '#fff'
  },
  actionText: {
      color: '#fff',
      textAlign: 'center',
      paddingLeft: 10,
      paddingRight: 10
  },
  text: {
    fontSize: 20,
  },
});

export default AlertScreen;