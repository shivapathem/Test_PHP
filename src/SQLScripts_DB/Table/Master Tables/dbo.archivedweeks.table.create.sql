USE [Allocate7]
GO

CREATE TABLE ArchivedWeeks 
   (
	[ID] [int] IDENTITY(1,1) NOT NULL,
	[WeekNumber] [int] NOT NULL
     CONSTRAINT PK_ArchivedWeeks PRIMARY KEY CLUSTERED 
      (
	    [ID] ASC
      )	
	)
	
CREATE NONCLUSTERED INDEX idx_ArchivedWeeks_Weeknumber ON ArchivedWeeks
(
	WeekNumber ASC
)	

insert into ArchivedWeeks(weeknumber) select distinct weeknumber from Allocations_archive;