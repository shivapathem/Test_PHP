USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_StaffCanRequestType]    Script Date: 02/11/2022 15:54:43 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER   PROCEDURE [dbo].[usp_get_StaffCanRequestType] 
	-- Add the parameters for the stored procedure here
	@intTypeID int
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;
	SELECT DISTINCT RequestTypesStaffLink.Login,CASE WHEN sp.ScheduledPersonID IS NULL THEN CASE WHEN (sd.PreferredForename IS NULL or sd.PreferredForename = '')
    THEN CASE WHEN (sd.Surname IS NULL or sd.Surname = '') THEN  sd.Forename ELSE (sd.Surname + ', ' + sd.Forename)END  ELSE
    CASE WHEN (sd.Surname IS NULL or sd.Surname = '') THEN  sd.PreferredForename ELSE (sd.Surname + ', ' + sd.PreferredForename) END   END
    ELSE CASE WHEN (sp.DisplayLastName IS NULL or sp.DisplayLastName = '') THEN sp.DisplayFirstName ELSE (sp.DisplayLastName + ', '+ sp.DisplayFirstName) END END AS Name FROM  RequestTypesStaffLink 
 JOIN StaffDetails  (NOLOCK)  sd ON RequestTypesStaffLink.Login = sd.NetLogin 
 JOIN  ScheduledPeople (NOLOCK) sp on sp.StaffDetailsID = sd.StaffID WHERE RequestTypeID=@intTypeID

END
