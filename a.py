class City:
    def __init__(self, name, stDG, stDS, node):
        self.f_n = stDG
        self.g_n = stDS
        self.total = self.f_n + self.g_n
        self.cityName = name
        self.node = node

class Node:
    def __init__(self, name, next_nodes=None):
        self.nodeName = name
        self.nextNodes = next_nodes or []
        self.Start = None

    def Initialization(self):
        # Create all nodes
        Arad = Node("Arad")
        Sibiu = Node("Sibiu")
        RimicuVilcea = Node("RimicuVilcea")
        Pitesti = Node("Pitesti")
        Zerind = Node("Zerind")
        Timisoara = Node("Timisoara")
        Craiova = Node("Craiova")
        Bucharest = Node("Bucharest")
        Oradea = Node("Oradea")
        Fagaras = Node("Fagaras")

        # Connections
        Arad.nextNodes = [
            City("Sibiu", 253, 140, Sibiu),
            City("Zerind", 374, 75, Zerind),
            City("Timisoara", 329, 118, Timisoara)
        ]

        Sibiu.nextNodes = [
            City("Arad", 366, 280, Arad),
            City("Fagaras", 176, 239, Fagaras),
            City("RimicuVilcea", 193, 220, RimicuVilcea),
            City("Oradea", 380, 291, Oradea)
        ]

        RimicuVilcea.nextNodes = [
            City("Pitesti", 100, 317, Pitesti),
            City("Craiova", 160, 366, Craiova),
            City("Sibiu", 253, 300, Sibiu)
        ]

        Pitesti.nextNodes = [
            City("RimicuVilcea", 193, 414, RimicuVilcea),
            City("Craiova", 160, 455, Craiova),
            City("Bucharest", 0, 418, Bucharest)
        ]

        # Start node
        self.Start = Arad
        print("Initialized")

    def Search(self):
        dest = "ab"
        curr = self.Start
        print(curr.nodeName)

        while dest != "Bucharest":
            min_total = curr.nextNodes[0].total
            psudoCurr = curr.nextNodes[0].node

            for i in range(1, len(curr.nextNodes)):
                if min_total > curr.nextNodes[i].total:
                    min_total = curr.nextNodes[i].total
                    psudoCurr = curr.nextNodes[i].node

            curr = psudoCurr
            dest = curr.nodeName
            print(dest)

# Main Program
if __name__ == "__main__":
    n = Node("dummy")
    n.Initialization()
    n.Search()
