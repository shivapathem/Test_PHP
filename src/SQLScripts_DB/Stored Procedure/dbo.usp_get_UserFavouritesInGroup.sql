USE [Allocate7]
GO
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[usp_get_UserFavouritesInGroup]') AND type in (N'P', N'PC'))
DROP PROCEDURE [dbo].[usp_get_UserFavouritesInGroup]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_UserFavouritesInGroup]    Script Date: 30/06/2021 13:46:09 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

-- =============================================
-- Author:		<Author,,Name>
-- Create date: <Create Date,,>
-- Description:	<Description,,>
-- =============================================
CREATE PROCEDURE [dbo].[usp_get_UserFavouritesInGroup]
 -- Add the parameters for the stored procedure here
    @intID int
AS
BEGIN
 -- SET NOCOUNT ON added to prevent extra result sets from
 -- interfering with SELECT statements.
    SET NOCOUNT ON;

    SELECT         user_favourites_staff_link.staffnumber, ScheduledPersonTeam_LINK.TeamID, StaffDetails.Surname + N', ' + StaffDetails.Forename AS FullName
    FROM           user_favourites_staff_link
                       INNER JOIN     StaffDetails ON user_favourites_staff_link.staffnumber = StaffDetails.StaffNumber
                       INNER JOIN     ScheduledPeople ON ScheduledPeople.StaffDetailsID = StaffDetails.StaffID
                       INNER JOIN     ScheduledPersonTeam_LINK ON ScheduledPersonTeam_LINK.ScheduledPersonID = ScheduledPeople.ScheduledPersonID
    WHERE          (user_favourites_staff_link.userfavouriteid = @intID)  and ScheduledPersonTeam_LINK.IsHomeTeam = 1 and ScheduledPersonTeam_LINK.scheduledType = 1
    GROUP BY       user_favourites_staff_link.staffnumber, ScheduledPersonTeam_LINK.TeamID, StaffDetails.Surname + N', ' + StaffDetails.Forename
    ORDER BY       FullName
END
GO

