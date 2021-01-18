import React, { Component } from 'react'
import { FlatList, Text, View, StyleSheet, StatusBar } from 'react-native'
import { TouchableOpacity } from 'react-native-gesture-handler';
import { getAlerts } from '../api/alerts.js'

class AlertsScreen extends Component {
  state = {
    alerts: []
  }

  componentDidMount() {
    getAlerts()
      .then(response => {
        return response.data.map(alert => ({
          id: `${alert.id}`,
          title: `${alert.title}`,
          description: `${alert.description}`,
          dateRaised: `${alert.dateRaised}`,
          assigned: `${alert.assigned}`,
          incidentId: `${alert.incidentId}`,
          status: `${alert.status}`
        }))
      })
      .then(alerts => {
        this.setState({ alerts: alerts });
      })
      .catch((err) => console.log(err));
  }

  renderItem = ({ item }) => (
    <View style={styles.item}>
      <TouchableOpacity onPress={() => this.onPress(item)}>
        <View style={styles.header}>
          <Text style={styles.title}>
            {item.title}
          </Text>
        </View>
        <View style={styles.body}>
          <Text>
            {item.dateRaised}
          </Text>
          <Text>
            {item.assigned !== '' ? item.assigned : 'Unassigned'}
          </Text>
          <Text style={styles.incidentId}>
            #{item.incidentId}
          </Text>
        </View>
      </TouchableOpacity>
    </View>
  );

  onPress = (item) => {
    return this.props.navigation.navigate('Alert', {
      alert: item,
    })
  }

  render() {
    return (
      <View>
        <FlatList
          data={this.state.alerts}
          renderItem={this.renderItem}
          keyExtractor={item => item.id}
        />
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
    borderTopLeftRadius: 20,
    borderTopRightRadius: 20,
  },
  body: {
    padding: 10,
    borderBottomLeftRadius: 20,
    borderBottomRightRadius: 20,
  },
  item: {
    marginVertical: 8,
    marginHorizontal: 16,
    paddingBottom: 10,
    borderWidth: 1,
    borderRadius: 20
  },
  title: {
    fontSize: 24,
  },
  incidentId: {
    textAlign: 'right'
  }
});

export default AlertsScreen;