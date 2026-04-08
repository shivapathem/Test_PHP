USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_fetch_RequestsByscheduledPersonID]    Script Date: 26/04/2022 18:58:16 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER PROCEDURE [dbo].[usp_fetch_RequestsByscheduledPersonID]  
	-- Add the parameters for the stored procedure here
	@strStartDate varchar(50),
	@strEndDate varchar(50),
	@schedulingPersonId varchar(50)
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	
	SET NOCOUNT ON;
  Declare @strQuery VARCHAR(MAX);
  SET @strQuery='SELECT Requests.dDate, RequestTypes.description + '' - '' + LeaveRequestGroups.Description AS Description, Requests.isOK, Requests.Approved, Requests.Unlikely, Requests.ID
  FROM Requests (nolock) 
  INNER JOIN RequestTypes (nolock)  ON Requests.RequestType = RequestTypes.ID 
  INNER JOIN LeaveRequestGroups (nolock)  ON RequestTypes.GroupID = LeaveRequestGroups.ID 
  WHERE (Requests.ScheduledPersonID = '''+@schedulingPersonId+''') 
  AND (Requests.dDate <= '''+@strEndDate+''') 
  AND (Requests.dDate >= '''+@strStartDate+''') 
  AND (Requests.Deleted = 0)';
	exec(@strQuery); 
END